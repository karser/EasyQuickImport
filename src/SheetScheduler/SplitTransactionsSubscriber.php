<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksAccountRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class SplitTransactionsSubscriber implements EventSubscriberInterface
{
    const ID = '40c25e85-9c3c-4d8c-a49c-3d516b22c860';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onScheduled(EntityOnScheduledEvent $event): void
    {
        $username = $event->getUser()->getQbUsername();
        Assert::notNull($username);

        $results = [];
        foreach ($event->getEntities() as $entity) {
            if (!$entity instanceof Transaction) {
                $results[] = $entity;
                continue;
            }

            /** @var QuickbooksAccountRepositoryInterface $repo */
            $repo = $this->em->getRepository(QuickbooksAccount::class);

            $creditAccount = $entity->getCreditAccount();
            $debitAccount = $entity->getDebitAccount();
            Assert::notNull($creditAccount);
            Assert::notNull($debitAccount);

            $fallbackCurrency = $entity->getCurrency();
            $creditCurrency = $repo->getCurrency($username, $creditAccount, QuickbooksAccount::TYPE_BANK) ?? $fallbackCurrency;
            $debitCurrency = $repo->getCurrency($username, $debitAccount, QuickbooksAccount::TYPE_BANK) ?? $fallbackCurrency;

            if ($creditCurrency === $debitCurrency) {
                $results[] = $entity;
            } else {
                $results[] = $this->createTransaction(
                    $entity->getTxnDate(), $entity->getExchangeRate(), $entity->getRefNumber(),
                    $creditCurrency, $creditAccount, $entity->getCreditAmount(), $entity->getCreditMemo(),
                    QuickbooksAccount::UNDEPOSITED_FUNDS, $entity->getCreditAmount(), $entity->getDebitMemo()
                );
                $results[] = $this->createTransaction(
                    $entity->getTxnDate(), self::ID, $entity->getRefNumber(),
                    $debitCurrency, QuickbooksAccount::UNDEPOSITED_FUNDS, $entity->getDebitAmount(), $entity->getCreditMemo(),
                    $debitAccount, $entity->getDebitAmount(), $entity->getDebitMemo()
                );
            }
        }
        $event->setEntities($results);
    }

    public function afterExchangeRateUpdated(EntityOnScheduledEvent $event): void
    {
        $username = $event->getUser()->getQbUsername();
        Assert::notNull($username);

        /** @var Transaction|null $prev */
        $prev = null;
        foreach ($event->getEntities() as $entity) {
            if (!$entity instanceof Transaction) {
                continue;
            }
            if ($prev !== null && $entity->getExchangeRate() === self::ID
                && (null !== $creditAmount = $prev->getCreditAmount())
                && (null !== $exchangeRate = $prev->getExchangeRate())
                && (null !== $debitAmount = $entity->getDebitAmount())
                && $entity->getCreditAccount() === QuickbooksAccount::UNDEPOSITED_FUNDS
                && $prev->getDebitAccount() === QuickbooksAccount::UNDEPOSITED_FUNDS
                && $creditAmount === $prev->getDebitAmount()
                && $entity->getCreditAmount() === $debitAmount
            ) {
                $creditExchangeRate = $this->getReversedExchangeRate((float)$debitAmount, (float)$exchangeRate, (float)$creditAmount);
                $entity->setExchangeRate($creditExchangeRate);
            } else {
                $prev = $entity;
            }
        }
    }

    private function getReversedExchangeRate(float $debitAmount, float $creditExchangeRate, float $creditAmount): ?string
    {
        $baseCurrencyAmount = $creditExchangeRate * $creditAmount;
        if ($debitAmount > 0) {
            return (string)($baseCurrencyAmount / $debitAmount);
        }
        return null;
    }

    private function createTransaction(?string $txnDate, ?string $exchangeRate, ?string $refNumber, ?string $currency,
                                    ?string $creditAccount, ?string $creditAmount, ?string $creditMemo,
                                    ?string $debitAccount, ?string $debitAmount, ?string $debitMemo): Transaction
    {
        $entity = new Transaction();

        $entity->setExchangeRate($exchangeRate);
        $entity->setCurrency($currency);

        $entity->setTxnDate($txnDate);
        $entity->setRefNumber($refNumber);

        $entity->setCreditAccount($creditAccount);
        $entity->setCreditAmount($creditAmount);
        $entity->setCreditMemo($creditMemo);
        $entity->setDebitAccount($debitAccount);
        $entity->setDebitAmount($debitAmount);
        $entity->setDebitMemo($debitMemo);

        return $entity;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityOnScheduledEvent::class => [
                ['onScheduled', EntityOnScheduledEvent::PRIORITY_MANUPILATE],
                ['afterExchangeRateUpdated', EntityOnScheduledEvent::PRIORITY_POST_UPDATE],
            ],
        ];
    }
}
