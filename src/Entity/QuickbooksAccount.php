<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuickbooksAccountRepository")
 * @ORM\Table(indexes={
 *   @ORM\Index(name="search_idx", columns={"full_name", "account_type"}),
 * })
 */
class QuickbooksAccount
{
    const UNDEPOSITED_FUNDS = 'Undeposited Funds';
    const UNCATEGORIZED_EXPENSES = 'Uncategorized Expenses';
    const UNCATEGORIZED_INCOME = 'Uncategorized Income';

    const TYPE_BANK = 'Bank';
    const TYPE_EQUITY = 'Equity';
    const TYPE_INCOME = 'Income';
    const TYPE_EXPENSE = 'Expense';
    const TYPE_COGS = 'CostOfGoodsSold';
    const TYPE_AP = 'AccountsPayable';
    const TYPE_AR = 'AccountsReceivable';
    const TYPE_OCA = 'OtherCurrentAsset';
    const TYPE_OCL = 'OtherCurrentLiability';
    const TYPE_FIXED_ASSET = 'FixedAsset';
    const TYPE_OTHER_INCOME = 'OtherIncome';


    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="QuickbooksCompany")
     * @ORM\JoinColumn(name="qb_username", referencedColumnName="qb_username", nullable=false)
     */
    private ?QuickbooksCompany $company = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=159)
     */
    private $fullName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $currency;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64)
     */
    private $accountType;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $specialAccountType;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $accountNumber;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

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

    /**
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    /**
     * @param string|null $fullName
     */
    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string|null $currency
     */
    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string|null
     */
    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    /**
     * @param string|null $accountType
     */
    public function setAccountType(?string $accountType): void
    {
        $this->accountType = $accountType;
    }

    /**
     * @return string|null
     */
    public function getSpecialAccountType(): ?string
    {
        return $this->specialAccountType;
    }

    /**
     * @param string|null $specialAccountType
     */
    public function setSpecialAccountType(?string $specialAccountType): void
    {
        $this->specialAccountType = $specialAccountType;
    }

    /**
     * @return string|null
     */
    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    /**
     * @param string|null $accountNumber
     */
    public function setAccountNumber(?string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }
}
