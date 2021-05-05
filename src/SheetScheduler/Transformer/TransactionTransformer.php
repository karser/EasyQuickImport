<?php declare(strict_types=1);

namespace App\SheetScheduler\Transformer;

use App\SheetScheduler\EntityTransformerInterface;
use App\SheetScheduler\Transaction;
use QuickBooks_QBXML_Object_JournalEntry;
use Webmozart\Assert\Assert;

class TransactionTransformer implements EntityTransformerInterface
{
    use TransformerContextTrait;

    public function supports(string $class): bool
    {
        return $class === Transaction::class;
    }

    /**
     * @param Transaction|object $entity
     */
    public function transform($entity): array
    {
        Assert::isInstanceOf($entity, Transaction::class);

        $journalEntry = $this->createEntry(
            $entity->getTxnDate(), $entity->getRefNumber(), $entity->getExchangeRate(), $entity->getCurrency(),
            $entity->getCreditAccount(), $entity->getCreditAmount(), $entity->getCreditMemo(),
            $entity->getDebitAccount(), $entity->getDebitMemo(), $entity->getDebitAmount()
        );

        return [
            [QUICKBOOKS_ADD_JOURNALENTRY, $journalEntry],
        ];
    }

    private function createEntry(?string $date, ?string $refNumber, ?string $exchangeRate, ?string $currency, ?string $creditAccount,
                                 ?string $creditAmount, ?string $creditMemo, ?string $debitAccount, ?string $debitMemo, ?string $debitAmount): QuickBooks_QBXML_Object_JournalEntry
    {

        $entry = new QuickBooks_QBXML_Object_JournalEntry();
        $entry->setTxnDate($date);
        $entry->setRefNumber($refNumber);

        if ($this->isMultiCurrencyEnabled()) {
            $entry->set('ExchangeRate', $exchangeRate);
            $entry->set('CurrencyRef FullName', $currency);
        }
        // amount from crediting side to the debited one
        $creditLine = new \QuickBooks_QBXML_Object_JournalEntry_JournalCreditLine();
        $creditLine->setAccountName($creditAccount);
        $creditLine->setAmount($this->getAmount($creditAmount));
        $creditLine->setMemo($creditMemo);
        $entry->addCreditLine($creditLine);
        $debitLine = new \QuickBooks_QBXML_Object_JournalEntry_JournalDebitLine();
        $debitLine->setAccountName($debitAccount);
        $debitLine->setAmount($this->getAmount($debitAmount));
        $debitLine->setMemo($debitMemo);
        $entry->addDebitLine($debitLine);

        return $entry;
    }

}
