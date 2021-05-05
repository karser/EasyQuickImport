<?php declare(strict_types=1);

namespace App\Tests\Unit\SheetScheduler\Transformer;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksCompany;
use App\SheetScheduler\Transaction;
use App\SheetScheduler\Transformer\TransactionTransformer;
use PHPUnit\Framework\TestCase;
use QuickBooks_QBXML_Object;

class TransactionTransformerTest extends TestCase
{
    /** @var TransactionTransformer */
    private $transformer;
    /** @var QuickbooksCompany */
    private $company;

    public function setUp(): void
    {
        $company = new QuickbooksCompany('test-company');
        $company->setMultiCurrencyEnabled(true);
        $this->company = $company;

        $this->transformer = new TransactionTransformer();
        $this->transformer->setCompany($this->company);
    }

    public function testExpense(): void
    {
        $transaction = new Transaction();
        $transaction->setTxnDate('2018-10-10');
        $transaction->setRefNumber('ref1');
        $transaction->setCurrency('US Dollar');
        $transaction->setExchangeRate('7.83704');
        $transaction->setCreditAccount('Bank USD');
        $transaction->setCreditMemo('credit memo');
        $transaction->setCreditAmount('1,400.00');
        $transaction->setDebitAccount(QuickbooksAccount::UNCATEGORIZED_EXPENSES);
        $transaction->setDebitMemo('debit memo');
        $transaction->setDebitAmount('1,400.00');

        $results = $this->transformer->transform($transaction);
        self::assertCount(1, $results);
        /** @var \QuickBooks_QBXML_Object_JournalEntry $object */
        [$action, $object] = $results[0];
        self::assertSame(QUICKBOOKS_ADD_JOURNALENTRY, $action);
        $actual = $this->toArray($object->asList(null));
        $expected = [
            'TxnDate' => '2018-10-10',
            'RefNumber' => 'ref1',
            'ExchangeRate' => '7.83704',
            'CurrencyRef FullName' => 'US Dollar',
            'JournalCreditLine' => [[
                'AccountRef FullName' => 'Bank USD',
                'Amount' => '1400.00',
                'Memo' => 'credit memo',
            ]],
            'JournalDebitLine' => [[
                'AccountRef FullName' => QuickbooksAccount::UNCATEGORIZED_EXPENSES,
                'Amount' => '1400.00',
                'Memo' => 'debit memo',
            ]],
        ];
        self::assertEquals($expected, $actual);
    }

    public function testTransferSameCurrency(): void
    {
        $transaction = new Transaction();
        $transaction->setTxnDate('2018-10-10');
        $transaction->setRefNumber('ref1');
        $transaction->setCurrency('US Dollar');
        $transaction->setExchangeRate('7.83704');
        $transaction->setCreditAccount('Bank1 USD');
        $transaction->setCreditMemo('credit memo');
        $transaction->setCreditAmount('400.00');
        $transaction->setDebitAccount('Bank2 USD');
        $transaction->setDebitMemo('debit memo');
        $transaction->setDebitAmount('400.00');

        $results = $this->transformer->transform($transaction);
        self::assertCount(1, $results);
        /** @var \QuickBooks_QBXML_Object_JournalEntry $object */
        [$action, $object] = $results[0];
        self::assertSame(QUICKBOOKS_ADD_JOURNALENTRY, $action);
        $actual = $this->toArray($object->asList(null));
        $expected = [
            'TxnDate' => '2018-10-10',
            'RefNumber' => 'ref1',
            'ExchangeRate' => '7.83704',
            'CurrencyRef FullName' => 'US Dollar',
            'JournalCreditLine' => [[
                'AccountRef FullName' => 'Bank1 USD',
                'Amount' => '400.00',
                'Memo' => 'credit memo',
            ]],
            'JournalDebitLine' => [[
                'AccountRef FullName' => 'Bank2 USD',
                'Amount' => '400.00',
                'Memo' => 'debit memo',
            ]],
        ];
        self::assertEquals($expected, $actual);
    }


    private function toArray(array $list): array
    {
        $array = [];

        foreach ($list as $key => $value) {
            if ($value instanceof QuickBooks_QBXML_Object) {
                $array[$key] = $this->toArray($value->asList(null));
            } else if (is_array($value)) {
                $array[$key] = $this->toArray($value);
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }
}
