<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Currency\CurrencyExchangerInterface;

class FakeCurrencyExchanger implements CurrencyExchangerInterface
{
    public function getExchangeRate(string $base, string $target, string $date): float
    {
        return 1;
    }
}
