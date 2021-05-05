<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *   @ORM\Index(name="quickbooks_ticket_id", columns={"quickbooks_ticket_id"}),
 *   @ORM\Index(name="qb_status", columns={"qb_status"}),
 *   @ORM\Index(name="qb_username", columns={"qb_username", "qb_action", "ident", "qb_status"}),
 *   @ORM\Index(name="priority", columns={"priority"})
 * })
 */
class QuickbooksQueue
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $quickbooksQueueId;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quickbooksTicketId;

    /**
     * @ORM\ManyToOne(targetEntity="QuickbooksCompany")
     * @ORM\JoinColumn(name="qb_username", referencedColumnName="qb_username", nullable=false)
     */
    private ?QuickbooksCompany $company = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=32)
     */
    private $qbAction;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=40)
     */
    private $ident;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $extra;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $qbxml;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=1)
     */
    private $qbStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $msg;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     */
    private $enqueueDatetime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dequeueDatetime;

    const STATUS_LABELS = [
        'q' => 'Queued',
        's' => 'Success',
        'i' => 'Processing',
        'e' => 'Error',
        'h' => 'Error',
    ];

    public function getQbUsername(): ?string
    {
        return null !== $this->company ? $this->company->getQbUsername() : null;
    }

    public function getCompanyName(): ?string
    {
        return null !== $this->company ? $this->company->getCompanyName() : null;
    }

    public function getCompany(): ?QuickbooksCompany
    {
        return $this->company;
    }

    public function setCompany(?QuickbooksCompany $company): void
    {
        $this->company = $company;
    }

    public function getStatusLabel(): ?string
    {
        return self::STATUS_LABELS[$this->qbStatus] ?? $this->qbStatus;
    }

    /**
     * @return int|null
     */
    public function getQuickbooksQueueId(): ?int
    {
        return $this->quickbooksQueueId;
    }

    /**
     * @param int|null $quickbooksQueueId
     */
    public function setQuickbooksQueueId(?int $quickbooksQueueId): void
    {
        $this->quickbooksQueueId = $quickbooksQueueId;
    }

    /**
     * @return int|null
     */
    public function getQuickbooksTicketId(): ?int
    {
        return $this->quickbooksTicketId;
    }

    /**
     * @param int|null $quickbooksTicketId
     */
    public function setQuickbooksTicketId(?int $quickbooksTicketId): void
    {
        $this->quickbooksTicketId = $quickbooksTicketId;
    }

    /**
     * @return string|null
     */
    public function getQbAction(): ?string
    {
        return $this->qbAction;
    }

    /**
     * @param string|null $qbAction
     */
    public function setQbAction(?string $qbAction): void
    {
        $this->qbAction = $qbAction;
    }

    /**
     * @return string|null
     */
    public function getIdent(): ?string
    {
        return $this->ident;
    }

    /**
     * @param string|null $ident
     */
    public function setIdent(?string $ident): void
    {
        $this->ident = $ident;
    }

    /**
     * @return string|null
     */
    public function getExtra(): ?string
    {
        return $this->extra;
    }

    /**
     * @return array|null
     */
    public function getExtraData(): ?array
    {
        if ($this->extra === null) {
            return null;
        }
        return unserialize($this->extra, ['allowed_classes' => false]);
    }

    /**
     * @param string|null $extra
     */
    public function setExtra(?string $extra): void
    {
        $this->extra = $extra;
    }

    /**
     * @return string|null
     */
    public function getQbxml(): ?string
    {
        return $this->qbxml;
    }

    /**
     * @param string|null $qbxml
     */
    public function setQbxml(?string $qbxml): void
    {
        $this->qbxml = $qbxml;
    }

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @param int|null $priority
     */
    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return string|null
     */
    public function getQbStatus(): ?string
    {
        return $this->qbStatus;
    }

    /**
     * @param string|null $qbStatus
     */
    public function setQbStatus(?string $qbStatus): void
    {
        $this->qbStatus = $qbStatus;
    }

    /**
     * @return string|null
     */
    public function getMsg(): ?string
    {
        return $this->msg;
    }

    /**
     * @param string|null $msg
     */
    public function setMsg(?string $msg): void
    {
        $this->msg = $msg;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnqueueDatetime(): ?\DateTime
    {
        return $this->enqueueDatetime;
    }

    /**
     * @param \DateTime|null $enqueueDatetime
     */
    public function setEnqueueDatetime(?\DateTime $enqueueDatetime): void
    {
        $this->enqueueDatetime = $enqueueDatetime;
    }

    /**
     * @return \DateTime|null
     */
    public function getDequeueDatetime(): ?\DateTime
    {
        return $this->dequeueDatetime;
    }

    /**
     * @param \DateTime|null $dequeueDatetime
     */
    public function setDequeueDatetime(?\DateTime $dequeueDatetime): void
    {
        $this->dequeueDatetime = $dequeueDatetime;
    }
}
