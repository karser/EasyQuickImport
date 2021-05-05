<?php declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class QuickbooksServerSendRequestXmlEvent extends Event
{
    private ?string $requestId; //The requestID of the request which caused this hook to be called
    private string $qbUsername; //The username of the QuickBooks user
    private string $hook; //The hook to call
    private string $err; //Any errors that occur will be passed back here
    private array $hookData; //An array of additional data to be passed to the hook
    private array $callbackConfig; //An array of additional callback data
    private bool $stopPropagation;

    public function __construct(?string $requestId, string $qbUsername, string $hook, string $err, array $hookData, array $callbackConfig)
    {
        $this->requestId = $requestId;
        $this->qbUsername = $qbUsername;
        $this->hook = $hook;
        $this->err = $err;
        $this->hookData = $hookData;
        $this->callbackConfig = $callbackConfig;
        $this->stopPropagation = false;
    }

    public function isStopPropagation(): bool
    {
        return $this->stopPropagation;
    }

    public function setStopPropagation(bool $stopPropagation): void
    {
        $this->stopPropagation = $stopPropagation;
    }

    /**
     * @return string|null
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @return string
     */
    public function getQbUsername(): string
    {
        return $this->qbUsername;
    }

    /**
     * @return string
     */
    public function getHook(): string
    {
        return $this->hook;
    }

    /**
     * @return string
     */
    public function getErr(): string
    {
        return $this->err;
    }

    /**
     * @param string $err
     */
    public function setErr(string $err): void
    {
        $this->err = $err;
    }

    /**
     * @return array
     */
    public function getHookData(): array
    {
        return $this->hookData;
    }

    /**
     * @return array
     */
    public function getCallbackConfig(): array
    {
        return $this->callbackConfig;
    }
}
