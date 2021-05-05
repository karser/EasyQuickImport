<?php declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class QuickbooksServerResponseEvent extends Event
{
    private $requestId;
    private $user;
    private $action;
    private $ident;
    private $extra;
    /** @var string|null set error to stop further request */
    private $err;
    private $lastActionTime;
    private $lastActionIdentTime;
    private $xml;
    private $qbIdentifier;
    private $callbackConfig;
    private $qbXml;

    /**
     * @param mixed $extra
     */
    public function __construct(string $requestId, string $user, string $action, string $ident, $extra,
                                ?string $err, ?int $lastActionTime, ?int $lastActionIdentTime,
                                string $xml, array $qbIdentifier, array $callbackConfig, ?string $qbXml)
    {
        $this->requestId = $requestId;
        $this->user = $user;
        $this->action = $action;
        $this->ident = $ident;
        $this->extra = $extra;
        $this->err = $err;
        $this->lastActionTime = $lastActionTime;
        $this->lastActionIdentTime = $lastActionIdentTime;
        $this->xml = $xml;
        $this->qbIdentifier = $qbIdentifier;
        $this->callbackConfig = $callbackConfig;
        $this->qbXml = $qbXml;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getIdent(): string
    {
        return $this->ident;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @return string|null
     */
    public function getErr(): ?string
    {
        return $this->err;
    }

    /**
     * @return int|null
     */
    public function getLastActionTime(): ?int
    {
        return $this->lastActionTime;
    }

    /**
     * @return int|null
     */
    public function getLastActionIdentTime(): ?int
    {
        return $this->lastActionIdentTime;
    }

    /**
     * @return string
     */
    public function getXml(): string
    {
        return $this->xml;
    }

    /**
     * @return array
     */
    public function getQbIdentifier(): array
    {
        return $this->qbIdentifier;
    }

    /**
     * @return array
     */
    public function getCallbackConfig(): array
    {
        return $this->callbackConfig;
    }

    /**
     * @return string|null
     */
    public function getQbXml(): ?string
    {
        return $this->qbXml;
    }

    /**
     * @param string|null $err
     */
    public function setErr(?string $err): void
    {
        $this->err = $err;
    }
}
