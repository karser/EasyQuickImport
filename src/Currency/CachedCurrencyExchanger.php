<?php declare(strict_types=1);

namespace App\Currency;

use App\Exception\RuntimeException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachedCurrencyExchanger implements CurrencyExchangerInterface
{
    /** @var CacheInterface */
    private $cache;

    /** @var CurrencyExchanger */
    private $decorated;

    public function __construct(CacheInterface $cache, CurrencyExchanger $decorated)
    {
        $this->cache = $cache;
        $this->decorated = $decorated;
    }

    public function getExchangeRate(string $base, string $target, string $date): float
    {
        $key = strtolower(implode('|', [$base, $target, $date]));
        try {
            $value = $this->cache->get($key, function (ItemInterface $item) use ($base, $target, $date): float {
//                $item->tag(["{$base}/{$target}", $date]);
                return $this->decorated->getExchangeRate($base, $target, $date);
            });
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException('Unable to obtain cached currency rate', $e->getCode(), $e);
        }

        return $value;
    }
}
