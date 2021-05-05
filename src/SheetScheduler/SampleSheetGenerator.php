<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Exception\RuntimeException;
use App\SheetScheduler;
use Symfony\Component\Serializer\SerializerInterface;

class SampleSheetGenerator
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function generateSampleCsv(string $type): string
    {
        $entities = [];
        switch ($type) {
            case SheetScheduler::TYPE_CUSTOMER:
                $entities[] = $this->getSampleCustomer();
                break;
            case SheetScheduler::TYPE_CUSTOMER_INVOICE:
                $entities[] = $this->getSampleCustomerInvoice();
                break;
            case SheetScheduler::TYPE_VENDOR_BILL:
                $entities[] = $this->getSampleVendorBill();
                break;
            case SheetScheduler::TYPE_VENDOR:
                $entities[] = $this->getSampleVendor();
                break;
            case SheetScheduler::TYPE_TRANSACTION:
                $entities = $this->getSampleTransactions();
                break;
            default:
                throw new RuntimeException("{$type} is not supported");
        }

        return $this->serializer->serialize($entities, 'csv');
    }

    private function getSampleVendor(?Vendor $entity = null): Vendor
    {
        $entity = $entity ?? new Vendor();

        $entity->setVendorFullname('Silo');
        $entity->setVendorCompanyName('Silo LIMITED');

        $entity->setAddr1('68 Tap Kwok Nam Path');
        $entity->setAddr2('Lok Sheuk Tsan');
        $entity->setCity('Hong Kong');
        $entity->setState('HK');
        $entity->setPostalcode('999077');
        $entity->setCountry('Hong Kong');

        $entity->setVendorType('Service Providers');
        $entity->setTerms('Due on receipt');
        $entity->setCurrency('US Dollar');
        return $entity;
    }

    private function getSampleTransactions(): array
    {
        $entities = [];

        $entities[] = $this->getTransaction('2018-10-10', '1', 'ref21', 'US Dollar',
            'Undeposited Funds', '500.00', 'credit memo',
            'Chase Savings USD', '500.00', 'debit memo');

        $entities[] = $this->getTransaction('2018-10-10', '1', 'ref22', 'US Dollar',
            'Chase Savings USD', '400.00', 'credit memo',
            'Ask My Accountant', '400.00', 'debit memo');

        $entities[] = $this->getTransaction('2020-04-01', '', 'ref23', 'Euro',
            'Chase Savings USD', '10.09', 'credit memo',
            'Chase Savings EUR', '9.02', 'debit memo');

        $entities[] = $this->getTransaction('2018-10-10', '', 'ref24', 'Euro',
            'Chase Savings EUR', '100.00', 'credit memo',
            'Ask My Accountant', '100.00', 'debit memo');

        return $entities;
    }

    private function getTransaction(?string $txnDate, ?string $exchangeRate, ?string $refNumber, ?string $currency,
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

    private function getSampleVendorBill(): VendorBill
    {
        $entity = new VendorBill();
        $this->getSampleVendor($entity);

        $entity->setMemo('Bill Memo');
        $entity->setApAccount('Accounts Payable');
        $entity->setRefNumber('2018-05-10-0001');
        $entity->setTxnDate('2018-05-10');
        $entity->setLine1AccountFullName('Rent Expense');
        $entity->setLine1Amount('10.00');
        $entity->setLine1Memo('Item1 Memo');
        $entity->setLine2AccountFullName('Rent Expense');
        $entity->setLine2Amount('15.00');
        $entity->setLine2Memo('Item2 Memo');
        $entity->setExchangeRate('1');

        return $entity;
    }

    private function getSampleCustomer(?Customer $entity = null): Customer
    {
        $entity = $entity ?? new Customer();

        $entity->setCustomerFullName('HomeBase');
        $entity->setFirstName('Long');
        $entity->setLastName('Allen');
        $entity->setCompanyName('HomeBase LLC');

        $entity->setAddr1('4420 Shadowmar Drive');
        $entity->setAddr2('Louisiana');
        $entity->setCity('New Orleans');
        $entity->setState('LA');
        $entity->setPostalcode('70112');
        $entity->setCountry('United States');

        $entity->setTerms('Due on receipt');
        $entity->setCurrency('US Dollar');

        return $entity;
    }

    private function getSampleCustomerInvoice(): CustomerInvoice
    {
        $entity = new CustomerInvoice();
        $this->getSampleCustomer($entity);

        $entity->setRefNumber('180510-0001');
        $entity->setTxnDate('2018-05-10');
        $entity->setExchangeRate('1');

        $entity->setInvoiceMemo('Invoice memo');
        $entity->setArAccount('Accounts Receivable');

        $entity->setLine1ItemName('Consulting');
        $entity->setLine1Desc('Item desc');
        $entity->setLine1Amount('10.00');
        $entity->setLine1Quantity('1');

        return $entity;
    }
}
