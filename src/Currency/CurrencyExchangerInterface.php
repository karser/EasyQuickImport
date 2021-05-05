<?php declare(strict_types=1);

namespace App\Currency;

use App\Exception\RuntimeException;

interface CurrencyExchangerInterface
{
    /**
     * @throws RuntimeException
     */
    public function getExchangeRate(string $base, string $target, string $date): float;
}
