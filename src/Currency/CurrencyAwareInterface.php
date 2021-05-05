<?php declare(strict_types=1);

namespace App\Currency;

interface CurrencyAwareInterface
{
    public function getCurrency(): ?string;

    public function getTxnDate(): ?string;

    public function getExchangeRate(): ?string;

    public function setExchangeRate(?string $exchangeRate): void;
}
