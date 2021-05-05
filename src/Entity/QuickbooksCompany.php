<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuickbooksCompanyRepository")
 * @ORM\Table(name="quickbooks_user")
 */
class QuickbooksCompany
{
    public const DEFAULT_DECIMAL_SYMBOL = '.';
    public const DEFAULT_DIGIT_GROUPING_SYMBOL = ',';

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=40)
     */
    private ?string $qbUsername = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $qbPassword = null;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="companies")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $companyName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $qbCompanyFile = null;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private ?string $baseCurrency = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $multiCurrencyEnabled = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $qbwcWaitBeforeNextUpdate = null;

    /**
     * @ORM\Column(name="qbwc_min_run_every_n_seconds", type="integer", nullable=true)
     */
    private ?int $qbwcMinRunEveryNSeconds = null;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private ?string $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTime $writeDatetime = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTime $touchDatetime = null;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private ?string $decimalSymbol;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private ?string $digitGroupingSymbol;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $xml = null;

    public function __construct(?string $qbUsername = null)
    {
        $this->qbUsername = $qbUsername ?? Uuid::uuid4()->toString();
        $this->status = 'e';
        $this->baseCurrency = 'USD';
        $this->decimalSymbol = self::DEFAULT_DECIMAL_SYMBOL;
        $this->digitGroupingSymbol = self::DEFAULT_DIGIT_GROUPING_SYMBOL;
        $this->qbwcMinRunEveryNSeconds = 0;
        $this->qbwcWaitBeforeNextUpdate = 0;
        $this->writeDatetime = new \DateTime();
        $this->touchDatetime = new \DateTime();
    }

    const STATUS_LABELS = [
//        'q' => 'Queued',
//        's' => 'Success',
//        'e' => 'Error',
    ];

    public function __toString(): string
    {
        return $this->companyName ?? $this->qbUsername ?? '';
    }

    public function getStatusLabel(): ?string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
        if (null !== $user) {
            $user->addCompany($this);
        }
    }

    public function isMultiCurrencyEnabled(): bool
    {
        return $this->multiCurrencyEnabled;
    }

    public function setMultiCurrencyEnabled(bool $multiCurrencyEnabled): void
    {
        $this->multiCurrencyEnabled = $multiCurrencyEnabled;
    }

    public function getDecimalSymbol(): ?string
    {
        return $this->decimalSymbol;
    }

    public function setDecimalSymbol(?string $decimalSymbol): void
    {
        $this->decimalSymbol = $decimalSymbol;
    }

    public function getDigitGroupingSymbol(): ?string
    {
        return $this->digitGroupingSymbol;
    }

    public function setDigitGroupingSymbol(?string $digitGroupingSymbol): void
    {
        $this->digitGroupingSymbol = $digitGroupingSymbol;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string|null
     */
    public function getQbUsername(): ?string
    {
        return $this->qbUsername;
    }

    /**
     * @param string|null $qbUsername
     */
    public function setQbUsername(?string $qbUsername): void
    {
        $this->qbUsername = $qbUsername !== null ? mb_strtolower($qbUsername) : null;
    }

    public function getQbPlainPassword(): ?string
    {
        return null;
    }

    public function setQbPlainPassword(?string $qbPlainPassword): void
    {
        $this->setQbPassword($qbPlainPassword);
    }

    /**
     * @return string|null
     */
    public function getQbPassword(): ?string
    {
        return $this->qbPassword;
    }

    /**
     * @param string|null $qbPassword
     */
    public function setQbPassword(?string $qbPassword): void
    {
        $this->qbPassword = null !== $qbPassword ? $this->_hash($qbPassword) : null;
    }

    private function _hash(string $password): string
    {
        $func = QUICKBOOKS_HASH;
        return $func($password . QUICKBOOKS_SALT);
    }

    /**
     * @return string|null
     */
    public function getQbCompanyFile(): ?string
    {
        return $this->qbCompanyFile;
    }

    /**
     * @param string|null $qbCompanyFile
     */
    public function setQbCompanyFile(?string $qbCompanyFile): void
    {
        $this->qbCompanyFile = $qbCompanyFile;
    }

    /**
     * @return int|null
     */
    public function getQbwcWaitBeforeNextUpdate(): ?int
    {
        return $this->qbwcWaitBeforeNextUpdate;
    }

    /**
     * @param int|null $qbwcWaitBeforeNextUpdate
     */
    public function setQbwcWaitBeforeNextUpdate(?int $qbwcWaitBeforeNextUpdate): void
    {
        $this->qbwcWaitBeforeNextUpdate = $qbwcWaitBeforeNextUpdate;
    }

    /**
     * @return int|null
     */
    public function getQbwcMinRunEveryNSeconds(): ?int
    {
        return $this->qbwcMinRunEveryNSeconds;
    }

    /**
     * @param int|null $qbwcMinRunEveryNSeconds
     */
    public function setQbwcMinRunEveryNSeconds(?int $qbwcMinRunEveryNSeconds): void
    {
        $this->qbwcMinRunEveryNSeconds = $qbwcMinRunEveryNSeconds;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime|null
     */
    public function getWriteDatetime(): ?\DateTime
    {
        return $this->writeDatetime;
    }

    /**
     * @param \DateTime|null $writeDatetime
     */
    public function setWriteDatetime(?\DateTime $writeDatetime): void
    {
        $this->writeDatetime = $writeDatetime;
    }

    /**
     * @return \DateTime|null
     */
    public function getTouchDatetime(): ?\DateTime
    {
        return $this->touchDatetime;
    }

    /**
     * @param \DateTime|null $touchDatetime
     */
    public function setTouchDatetime(?\DateTime $touchDatetime): void
    {
        $this->touchDatetime = $touchDatetime;
    }

    /**
     * @return string|null
     */
    public function getBaseCurrency(): ?string
    {
        return $this->baseCurrency;
    }

    /**
     * @param string|null $baseCurrency
     */
    public function setBaseCurrency(?string $baseCurrency): void
    {
        $this->baseCurrency = $baseCurrency;
    }

    public function getXml(): ?string
    {
        return $this->xml;
    }

    public function setXml(?string $xml): void
    {
        $this->xml = $xml;
    }
}
