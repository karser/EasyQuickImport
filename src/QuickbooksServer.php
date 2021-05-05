<?php declare(strict_types=1);

namespace App;

use App\Entity\QuickbooksCompany;
use App\Event\QuickbooksServerResponseEvent;
use App\Event\QuickbooksServerSendRequestXmlEvent;
use Psr\Log\LoggerInterface;
use QuickBooks_Driver_Factory;
use QuickBooks_Driver_Sql_Mysqli;
use QuickBooks_Utilities;
use QuickBooks_WebConnector_Handlers;
use QuickBooks_WebConnector_Queue;
use QuickBooks_WebConnector_Queue_Singleton;
use QuickBooks_WebConnector_Server;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class QuickbooksServer implements QuickbooksServerInterface
{
    private $dsn;
    private $appName;
    private $projectUrl;
    private $logger;
    private $eventDispatcher;

    public function __construct(string $dsn, string $appName, string $projectUrl,
                                LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->dsn = str_replace('mysql://', 'mysqli://', $dsn);
        $this->appName = $appName;
        $this->projectUrl = $projectUrl;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function truncateQueue(): void
    {
        /** @var QuickBooks_Driver_Sql_Mysqli $driver */
        $driver = QuickBooks_Driver_Factory::create($this->dsn);
        $driver->query('TRUNCATE TABLE quickbooks_queue', $errNum, $errMsg);
        $driver->query('TRUNCATE TABLE quickbooks_ticket', $errNum, $errMsg);
    }

    public function createCompany(string $username, string $password): void
    {
        QuickBooks_Utilities::createUser($this->dsn, $username, $password);
    }

    public function schedule(?string $username, string $action, string $id, ?string $qbXml = null, ?array $extra = null): bool
    {
        $Queue = new QuickBooks_WebConnector_Queue($this->dsn);
        return $Queue->enqueue($action, $id, $priority = 0, $extra, $username, $qbXml);
    }

    public function config(QuickbooksCompany $company): string
    {
        $username = $company->getQbUsername();
        Assert::notNull($username);
        $companyName = $company->getCompanyName();
        Assert::notNull($companyName);
        $guid = $username;//$this->createGUID($username);
        $qwc = new \QuickBooks_WebConnector_QWC(
            "{$this->appName} for {$companyName}",
            "User: {$companyName}",
            $this->projectUrl . '/qbwc',
            $this->projectUrl,
            $username,
            $guid,
            $guid,
            QUICKBOOKS_TYPE_QBFS,
            $readonly = false,
            $company->getQbwcMinRunEveryNSeconds()
        );
        return $qwc->generate();
    }

    private function createGUID(string $string): string
    {
        // GUID is 128-bit hex
        $hash = md5($string);
        // Create formatted GUID
        $guid = '';
        // GUID format is XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX for readability
        $guid .= substr($hash,  0,  8) .
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12);

        return $guid;
    }


    public function qbwc(?string $input): string
    {
        //Configure the logging level
        $log_level = QUICKBOOKS_LOG_DEVELOP;

        //Pure-PHP SOAP server
        $soapserver = QUICKBOOKS_SOAPSERVER_BUILTIN;

        //we can turn this off
        $handler_options = [
            'deny_concurrent_logins' => false,
            'deny_reallyfast_logins' => false,
        ];

        // The next three params $map, $errmap, and $hooks are callbacks which
        // will be called when certain actions/events/requests/responses occur within
        // the framework
        $map = [
            '*' => [[$this, '_qbRequest'], [$this, '_qbResponse']],
        ];
        $errmap = [
            '*' => [$this, '_catchallErrors'],
        ];
        $hooks = array(
            QuickBooks_WebConnector_Handlers::HOOK_LOGINSUCCESS => [[$this,'_loginSuccess']], // requires vendor/consolibyte/quickbooks/QuickBooks/WebConnector/Handlers.php
            QUICKBOOKS_HANDLERS_HOOK_SENDREQUESTXML => [[$this,'_sendRequestXml']],
            QUICKBOOKS_HANDLERS_HOOK_LOGINFAILURE => [[$this,'_loginFail']],
        );

        //To be used with singleton queue
        $driver_options = [];

        //Callback options, not needed at the moment
        $callback_options = [];

        //nothing needed here at the moment
        $soap_options = [];

        //construct a new instance of the web connector server
        $Server = new QuickBooks_WebConnector_Server($this->dsn, $map, $errmap, $hooks, $log_level, $soapserver, QUICKBOOKS_WSDL, $soap_options, $handler_options, $driver_options, $callback_options);

        //_input is from file_get_contents('php://input')
        $class = new ReflectionClass(QuickBooks_WebConnector_Server::class);
        $property = $class->getProperty('_input');
        $property->setAccessible(true);
        $property->setValue($Server, $input);

        ob_start();
        $Server->handle(false, true);
        $result = ob_get_clean();
        Assert::string($result);
        return $result;
    }

    public function init(): void
    {
        if (!QuickBooks_Utilities::initialized($this->dsn)) {
            QuickBooks_Utilities::initialize($this->dsn);
        }
        QuickBooks_WebConnector_Queue_Singleton::initialize($this->dsn);
    }

    public function _loginSuccess(?string $requestId, string $user, string $hook, ?string &$err,
                                           array $hook_data, array $callbackConfig): void
    {
    }

    public function _loginFail(?string $requestId, string $user, string $action, string $ident, ?array $extra, ?array $err): void
    {
        $this->logger->error('QuickbooksServer::_loginFail: '.$action, func_get_args());
    }

    /**
     * @param mixed|null $extra
     * @param mixed|null $errNum
     */
    public function _catchallErrors(?string $requestId, string $user, ?string $action, ?string $ident, $extra,
                                    ?string &$err, ?string $xml, $errNum, string $errMsg, array $callbackConfig): bool
    {
        $this->logger->error('QuickbooksServer::_catchallErrors', func_get_args());
        return $continueOnError = true;
    }

    /**
     * @param mixed $extra
     * @return string xml or QUICKBOOKS_NOOP trim
     */
    public function _qbRequest(string $requestId, string $user, string $action, string $ident, $extra,
                               string &$err, ?int $lastActionTime, ?int $lastActionIdentTime,
                               string $version, string $locale, array $callbackConfig, ?string $qbXml): ?string
    {
        //$err no matter
        return $qbXml;
    }

    /**
     * @param mixed $extra
     */
    public function _qbResponse(string $requestId, string $user, string $action, string $ident, $extra,
                                ?string &$err, ?int $lastActionTime, ?int $lastActionIdentTime,
                                string $xml, array $qbIdentifier, array $callbackConfig, ?string $qbXml): void
    {
        $event = new QuickbooksServerResponseEvent($requestId, $user, $action, $ident, $extra,
            $err, $lastActionTime, $lastActionIdentTime, $xml, $qbIdentifier, $callbackConfig, $qbXml);
        $this->eventDispatcher->dispatch($event);
        $err = $event->getErr();
    }


    public function _sendRequestXml(?string $requestId, string $qbUsername, string $hook, string &$err, array $hookData, array $callbackConfig): bool
    {
        $event = new QuickbooksServerSendRequestXmlEvent($requestId, $qbUsername, $hook, $err, $hookData, $callbackConfig);
        $this->eventDispatcher->dispatch($event);
        $err = $event->getErr();
        return !$event->isStopPropagation();
    }
}
