<?php declare(strict_types=1);

namespace App\Currency;

use App\Exception\RuntimeException;
use App\SheetScheduler\EntityOnScheduledEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateRateOnEntityScheduledSubscriber implements EventSubscriberInterface
{
    /** @var CurrencyExchangerInterface */
    private $currencyExchanger;

    public function __construct(CurrencyExchangerInterface $currencyExchanger)
    {
        $this->currencyExchanger = $currencyExchanger;
    }

    public function onScheduled(EntityOnScheduledEvent $event): void
    {
        $user = $event->getUser();
        $base = $user->getBaseCurrency();
        if ($base === null || trim($base) === '') {
            return;
        }
        foreach ($event->getEntities() as $entity) {
            if (!$entity instanceof CurrencyAwareInterface || !$this->isEligible($entity)) {
                continue;
            }
            $this->updateRate($entity, $base, $event->getLine());
        }
    }

    private function updateRate(CurrencyAwareInterface $entity, string $base, int $line): void
    {
        $txnDate = $this->normalizeDate($entity->getTxnDate());
        $currencyMap = new CurrencyMap();

        try {
            [$target] = $currencyMap->findCurrency($entity->getCurrency());
        } catch (RuntimeException $e) {
            throw new RuntimeException("Unable to find currency for {$entity->getCurrency()}", $e->getCode(), $e);
        }
        try {
            $rate = $this->currencyExchanger->getExchangeRate($base, $target, $txnDate);
            $entity->setExchangeRate((string)$rate);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Unable to update currency rate for {$base}/{$target}, date: {$txnDate}, line {$line}", $e->getCode(), $e);
        }
    }

    private function isEligible(CurrencyAwareInterface $entity): bool
    {
        $exchangeRate = $entity->getExchangeRate();
        if (!($exchangeRate === null || trim($exchangeRate) === '')) {
            return false;
        }
        $targetCurrency = $entity->getCurrency();
        if ($targetCurrency === null || trim($targetCurrency) === '') {
            return false;
        }
        return true;
    }

    private function normalizeDate(?string $date): string
    {
        if (($date !== null) && (trim($date) === '')) {
            $date = null;
        }
        if ($date === null) {
            $date = date('Y-m-d');
        }

        return $date;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityOnScheduledEvent::class => ['onScheduled', EntityOnScheduledEvent::PRIORITY_UPDATE],
        ];
    }
}
