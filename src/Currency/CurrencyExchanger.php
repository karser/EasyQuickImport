<?php declare(strict_types=1);

namespace App\Currency;

use App\Exception\RuntimeException;
use DateTime;
use Exchanger\Contract\ExchangeRateService;
use Exchanger\Service\EuropeanCentralBank;
use Exchanger\ExchangeRateQueryBuilder;
use Exchanger\Service\Chain;
use Http\Adapter\Guzzle6\Client;
use Psr\Http\Message\RequestFactoryInterface;

class CurrencyExchanger implements CurrencyExchangerInterface
{
    private $requestFactory;

    public function __construct(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function getExchangeRate(string $base, string $target, string $date): float
    {
        if ($base === $target) {
            return 1.0;
        }
        $client = new Client();
        $exchangerEuroBased = new Chain([
            new EuropeanCentralBank($client, $this->requestFactory),
        ]);
        try {
            $rateBase = $this->getInternalExchangeRate($exchangerEuroBased, "EUR/{$base}", $date);
            $rateTarget = $this->getInternalExchangeRate($exchangerEuroBased, "EUR/{$target}", $date);
            return $rateBase / $rateTarget;
        } catch (\Throwable $e) {
            throw new RuntimeException("Unable to get currency rate. Currency pair: {$base}/{$target}, date: {$date}. Error: {$e->getMessage()}");
        }
    }

    public function getInternalExchangeRate(ExchangeRateService $exchanger, string $currencyPair, string $date): float
    {
        $query = new ExchangeRateQueryBuilder($currencyPair);
        $query->setDate(new DateTime($date));
        return $exchanger->getExchangeRate($query->build())->getValue();
    }
}
