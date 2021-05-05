<?php declare(strict_types=1);

namespace App\Tests\Functional;

ob_start(); //QuickBooks_WebConnector_Server submits header()

use App\Entity\QuickbooksCompany;
use App\Entity\QuickbooksQueue;
use App\Entity\User;
use App\QuickbooksServer;
use App\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class QuickbooksServerTest extends WebTestCase
{
    const USERNAME = 'quickbooks';
    const PASSWORD = 'FE7XjHVzjfdH';

    /** @var KernelBrowser */
    private $client;
    /** @var User */
    private $user;

    public function setUp(): void
    {
        $this->client = self::createClient();

        /** @var UserService $userService */
        $userService = static::$container->get(UserService::class);
        $this->user = $userService->createUser('admin@localhost', 'pass', ['ROLE_ADMIN']);

        ob_flush();
    }
    public function tearDown(): void
    {
        ob_flush();
    }

    public function testExchange(): void
    {
        /** @var QuickbooksServer $server */
        $server = static::$container->get(QuickbooksServer::class);
        $server->truncateQueue();
        $this->givenCompany($this->user, self::USERNAME, self::PASSWORD);

        $customerAddXml = $this->getFixture('add_customer.xml');
        $server->schedule(self::USERNAME,QUICKBOOKS_ADD_CUSTOMER, '100', $customerAddXml);


        #authentication
        $request = $this->getFixture('authenticate_request.xml');
        $this->patchServer('POST', '/qbwc');
        $this->client->request('POST', '/qbwc', [], [], [], $request);

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, preg_match('|<ns1:string>(.+)</ns1:string>|', $response->getContent(), $matches));
        $ticketId = $matches[1];

        $expectedResponse = $this->getFixture('authenticate_response.xml', ['{ticketId}' => $ticketId]);
        self::assertSame($expectedResponse, $response->getContent());

        #task request
        $request = $this->getFixture('task_request.xml', ['{ticketId}' => $ticketId]);
        $this->patchServer('POST', '/qbwc');
        $this->client->request('POST', '/qbwc', [], [], [], $request);

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());

        $expectedResponse = $this->getFixture('task_add_customer_response.xml');
        self::assertSame($expectedResponse, $response->getContent());

        #task request added
        $request = $this->getFixture('task_add_customer_added_request.xml', ['{ticketId}' => $ticketId]);
        $this->patchServer('POST', '/qbwc');
        $this->client->request('POST', '/qbwc', [], [], [], $request);

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());

        $expectedResponse = $this->getFixture('task_add_customer_added_received_response.xml');
        self::assertSame($expectedResponse, $response->getContent());

        #bye
        $request = $this->getFixture('bye_request.xml', ['{ticketId}' => $ticketId]);
        $this->patchServer('POST', '/qbwc');
        $this->client->request('POST', '/qbwc', [], [], [], $request);

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());

        $expectedResponse = $this->getFixture('bye_response.xml');
        self::assertSame($expectedResponse, $response->getContent());
    }

    public function testSchedule(): void
    {
        self::bootKernel();

        /** @var QuickbooksServer $server */
        $server = static::$container->get(QuickbooksServer::class);
        $server->truncateQueue();

        /** @var EntityManagerInterface $em */
        $em = static::$container->get(EntityManagerInterface::class);
        $repo = $em->getRepository(QuickbooksQueue::class);
        self::assertEmpty($repo->findAll());

        #WHEN
        $server->schedule(self::USERNAME,QUICKBOOKS_ADD_CUSTOMER, '100', '<xml/>', ['a' => 'b']);

        #THEN
        $items = $repo->findAll();
        self::assertCount(1, $items);
        [$item] = $items;

        self::assertSame(QUICKBOOKS_ADD_CUSTOMER, $item->getQbAction());
        self::assertSame('100', $item->getIdent());
        self::assertSame('<xml/>', $item->getQbxml());
        self::assertSame(['a' => 'b'], $item->getExtraData());
    }

    public function testDownloadQBWCConfig(): void
    {
        $this->givenCompany($this->user, self::USERNAME, self::PASSWORD);
        $this->logIn($this->user);

        $this->client->request('GET', '/download-qbwc-config?id='.self::USERNAME);
        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringStartsWith('<?xml version="1.0"?>', $response->getContent());
        self::assertSame('text/xml; charset=UTF-8', $response->headers->get('content-type'));
        self::assertStringStartsWith('attachment; filename=', $response->headers->get('content-disposition'));
    }

    private function givenCompany(User $user, string $username, ?string $password): QuickbooksCompany
    {
        /** @var EntityManagerInterface $em */
        $em = static::$container->get(EntityManagerInterface::class);
        $c = $em->getRepository(QuickbooksCompany::class)->findOneBy(['qbUsername' => $username]);
        if (null === $c) {
            $c = new QuickbooksCompany($username);
            $em->persist($c);
        }
        $c->setCompanyName($username);
        $c->setQbPassword($password);
        $c->setUser($user);
        $em->flush();

        return $c;
    }

    private function logIn(User $user)
    {
        $session = self::$container->get('session');

        $firewallName = 'main';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'main';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testInfo(): void
    {
        $this->patchServer('GET', '/qbwc');
        $this->client->request('GET', '/qbwc');
        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Easy Quick Import', $response->getContent());
        self::assertSame('text/plain; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function xtestWSDL(): void //exits so not testable
    {
        $this->patchServer('GET', '/qbwc?wsdl');
        $this->client->request('GET', '/qbwc?wsdl');
        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringStartsWith('<?xml version="1.0"?>', $response->getContent());
        self::assertSame('text/xml; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function testNoDataExchangeRequired(): void
    {
        #server version exchange
        $request = $this->getFixture('server_version_request.xml');
        $this->patchServer('POST', '/qbwc');
        $this->client->request('POST', '/qbwc', [], [], [], $request);

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame($this->getFixture('server_version_response.xml'), $response->getContent());

        #client version exchange
        $request = $this->getFixture('client_version_request.xml');
        $this->patchServer('POST', '/qbwc');
        $this->client->request('POST', '/qbwc', [], [], [], $request);

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame($this->getFixture('client_version_response.xml'), $response->getContent());

        #authentication
        $request = $this->getFixture('authenticate_request.xml');
        $this->patchServer('POST', '/qbwc');
        $this->client->request('POST', '/qbwc', [], [], [], $request);

        $response = $this->client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertNotFalse(strpos($response->getContent(), '<ns1:authenticateResult>'), $response->getContent());
    }

    private function getFixture(string $name, ?array $replacements = null): string
    {
        $content = file_get_contents(__DIR__ . '/fixtures/' . $name);

        if ($replacements !== null) {
            foreach ($replacements as $search => $replacement) {
                $content = str_replace($search, $replacement, $content);
            }
        }
        return $content;
    }

    private function patchServer(string $method, string $uri): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $query = parse_url($uri, PHP_URL_QUERY);
        if (null !== $query) {
            parse_str($query, $_GET);
        }
    }
}
