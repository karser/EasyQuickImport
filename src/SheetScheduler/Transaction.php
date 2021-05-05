<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Currency\CurrencyAwareInterface;

class Transaction implements CurrencyAwareInterface
{
    /** @var string|null */
    private $txnDate;

    /** @var string|null */
    private $refNumber;

    /** @var string|null */
    private $currency;

    /** @var string|null */
    private $exchangeRate;

    /** @var string|null */
    private $creditAccount;

    /** @var string|null */
    private $creditMemo;

    /** @var string|null */
    private $creditAmount;

    /** @var string|null */
    private $debitAccount;

    /** @var string|null */
    private $debitMemo;

    /** @var string|null */
    private $debitAmount;

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
    public function getCreditAccount(): ?string
    {
        return $this->creditAccount;
    }

    /**
     * @param string|null $creditAccount
     */
    public function setCreditAccount(?string $creditAccount): void
    {
        $this->creditAccount = $creditAccount;
    }

    /**
     * @return string|null
     */
    public function getCreditMemo(): ?string
    {
        return $this->creditMemo;
    }

    /**
     * @param string|null $creditMemo
     */
    public function setCreditMemo(?string $creditMemo): void
    {
        $this->creditMemo = $creditMemo;
    }

    /**
     * @return string|null
     */
    public function getCreditAmount(): ?string
    {
        return $this->creditAmount;
    }

    /**
     * @param string|null $creditAmount
     */
    public function setCreditAmount(?string $creditAmount): void
    {
        $this->creditAmount = $creditAmount;
    }

    /**
     * @return string|null
     */
    public function getDebitAccount(): ?string
    {
        return $this->debitAccount;
    }

    /**
     * @param string|null $debitAccount
     */
    public function setDebitAccount(?string $debitAccount): void
    {
        $this->debitAccount = $debitAccount;
    }

    /**
     * @return string|null
     */
    public function getDebitMemo(): ?string
    {
        return $this->debitMemo;
    }

    /**
     * @param string|null $debitMemo
     */
    public function setDebitMemo(?string $debitMemo): void
    {
        $this->debitMemo = $debitMemo;
    }

    /**
     * @return string|null
     */
    public function getDebitAmount(): ?string
    {
        return $this->debitAmount;
    }

    /**
     * @param string|null $debitAmount
     */
    public function setDebitAmount(?string $debitAmount): void
    {
        $this->debitAmount = $debitAmount;
    }
}
