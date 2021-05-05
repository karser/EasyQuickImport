<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Currency\CurrencyAwareInterface;

class VendorBill extends Vendor implements CurrencyAwareInterface
{
    /** @var string|null */
    private $memo;

    /** @var string|null */
    private $apAccount;

    /** @var string|null */
    private $refNumber;

    /** @var string|null */
    private $txnDate;

    /** @var string|null */
    private $exchangeRate;

    /** @var string|null */
    private $line1AccountFullName;

    /** @var string|null */
    private $line1Amount;

    /** @var string|null */
    private $line1Memo;

    /** @var string|null */
    private $line2AccountFullName;

    /** @var string|null */
    private $line2Amount;

    /** @var string|null */
    private $line2Memo;

    /**
     * @return string|null
     */
    public function getMemo(): ?string
    {
        return $this->memo;
    }

    /**
     * @param string|null $memo
     */
    public function setMemo(?string $memo): void
    {
        $this->memo = $memo;
    }

    /**
     * @return string|null
     */
    public function getApAccount(): ?string
    {
        return $this->apAccount;
    }

    /**
     * @param string|null $apAccount
     */
    public function setApAccount(?string $apAccount): void
    {
        $this->apAccount = $apAccount;
    }

    /**
     * @return string|null
     */
    public function getRefNumber(): ?string
    {
        return $this->refNumber;
    }

    /**
     * @param string|null $refNumber
     */
    public function setRefNumber(?string $refNumber): void
    {
        $this->refNumber = $refNumber;
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
    public function getLine1AccountFullName(): ?string
    {
        return $this->line1AccountFullName;
    }

    /**
     * @param string|null $line1AccountFullName
     */
    public function setLine1AccountFullName(?string $line1AccountFullName): void
    {
        $this->line1AccountFullName = $line1AccountFullName;
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
    public function getLine1Memo(): ?string
    {
        return $this->line1Memo;
    }

    /**
     * @param string|null $line1Memo
     */
    public function setLine1Memo(?string $line1Memo): void
    {
        $this->line1Memo = $line1Memo;
    }

    /**
     * @return string|null
     */
    public function getLine2AccountFullName(): ?string
    {
        return $this->line2AccountFullName;
    }

    /**
     * @param string|null $line2AccountFullName
     */
    public function setLine2AccountFullName(?string $line2AccountFullName): void
    {
        $this->line2AccountFullName = $line2AccountFullName;
    }

    /**
     * @return string|null
     */
    public function getLine2Amount(): ?string
    {
        return $this->line2Amount;
    }

    /**
     * @param string|null $line2Amount
     */
    public function setLine2Amount(?string $line2Amount): void
    {
        $this->line2Amount = $line2Amount;
    }

    /**
     * @return string|null
     */
    public function getLine2Memo(): ?string
    {
        return $this->line2Memo;
    }

    /**
     * @param string|null $line2Memo
     */
    public function setLine2Memo(?string $line2Memo): void
    {
        $this->line2Memo = $line2Memo;
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
}
