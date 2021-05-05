<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Currency\CurrencyAwareInterface;
use App\Exception\RuntimeException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerInvoice extends Customer implements CurrencyAwareInterface
{
    /** @var string|null */
    private $refNumber;

    /** @var string|null */
    private $invoiceMemo;

    /** @var string|null */
    private $arAccount;

    /** @var string|null */
    private $txnDate;

    /** @var string|null */
    private $exchangeRate;

    /** @var string|null */
    private $line1ItemName;

    /** @var string|null */
    private $line1Desc;

    /** @var string|null */
    private $line1Quantity;

    /** @var string|null */
    private $line1Amount;

    /** @var string|null */
    private $line1Rate;

    public function validateQuantity(ExecutionContextInterface $context): void
    {
        try {
            LineItemLogic::getQuantity($this->line1Quantity, $this->line1Rate, $this->line1Amount);
        } catch (RuntimeException $e) {
            $context->addViolation($e->getMessage());
        }
    }

    public function getRefNumber(): ?string
    {
        return $this->refNumber;
    }

    public function setRefNumber(?string $refNumber): void
    {
        $this->refNumber = $refNumber;
    }

    /**
     * @return string|null
     */
    public function getInvoiceMemo(): ?string
    {
        return $this->invoiceMemo;
    }

    /**
     * @param string|null $invoiceMemo
     */
    public function setInvoiceMemo(?string $invoiceMemo): void
    {
        $this->invoiceMemo = $invoiceMemo;
    }

    /**
     * @return string|null
     */
    public function getArAccount(): ?string
    {
        return $this->arAccount;
    }

    /**
     * @param string|null $arAccount
     */
    public function setArAccount(?string $arAccount): void
    {
        $this->arAccount = $arAccount;
    }

    /**
     * @return string|null
     */
    public function getTxnDate(): ?string
    {
        return $this->txnDate;
    }

    /**
     * @param string|null $txnDate
     */
    public function setTxnDate(?string $txnDate): void
    {
        $this->txnDate = $txnDate;
    }

    /**
     * @return string|null
     */
    public function getExchangeRate(): ?string
    {
        return $this->exchangeRate;
    }

    /**
     * @param string|null $exchangeRate
     */
    public function setExchangeRate(?string $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return string|null
     */
    public function getLine1ItemName(): ?string
    {
        return $this->line1ItemName;
    }

    /**
     * @param string|null $line1ItemName
     */
    public function setLine1ItemName(?string $line1ItemName): void
    {
        $this->line1ItemName = $line1ItemName;
    }

    /**
     * @return string|null
     */
    public function getLine1Desc(): ?string
    {
        return $this->line1Desc;
    }

    /**
     * @param string|null $line1Desc
     */
    public function setLine1Desc(?string $line1Desc): void
    {
        $this->line1Desc = $line1Desc;
    }

    /**
     * @return string|null
     */
    public function getLine1Quantity(): ?string
    {
        return $this->line1Quantity;
    }

    /**
     * @param string|null $line1Quantity
     */
    public function setLine1Quantity(?string $line1Quantity): void
    {
        $this->line1Quantity = $line1Quantity;
    }

    /**
     * @return string|null
     */
    public function getLine1Amount(): ?string
    {
        return $this->line1Amount;
    }

    /**
     * @param string|null $line1Amount
     */
    public function setLine1Amount(?string $line1Amount): void
    {
        $this->line1Amount = $line1Amount;
    }

    /**
     * @return string|null
     */
    public function getLine1Rate(): ?string
    {
        return $this->line1Rate;
    }

    /**
     * @param string|null $line1Rate
     */
    public function setLine1Rate(?string $line1Rate): void
    {
        $this->line1Rate = $line1Rate;
    }
}
