<?php declare(strict_types=1);

namespace App\SheetScheduler\Transformer;

use App\SheetScheduler\EntityTransformerInterface;
use App\SheetScheduler\LineItemLogic;
use App\SheetScheduler\VendorBill;
use QuickBooks_QBXML_Object_Bill;
use QuickBooks_QBXML_Object_Bill_ExpenseLine;
use Webmozart\Assert\Assert;

class VendorBillTransformer implements EntityTransformerInterface
{
    use TransformerContextTrait;

    /** @var VendorTransformer */
    private $vendorTransformer;

    public function __construct(VendorTransformer $vendorTransformer)
    {
        $this->vendorTransformer = $vendorTransformer;
    }

    public function supports(string $class): bool
    {
        return $class === VendorBill::class;
    }

    /**
     * @param VendorBill|object $entity
     */
    public function transform($entity): array
    {
        Assert::isInstanceOf($entity, VendorBill::class);
        $this->vendorTransformer->setCompany($this->company);

        $bill = new QuickBooks_QBXML_Object_Bill();
        $bill->setVendorFullname($entity->getVendorFullname());
        $bill->setMemo($entity->getMemo());

        $terms = $entity->getTerms();
        if ($terms !== null) {
            $bill->set('TermsRef FullName', $terms);
        }
        if (null !== $apAccount = $entity->getApAccount()) {
            $bill->set('APAccountRef FullName', $apAccount);
        }

        $bill->setRefNumber($entity->getRefNumber());
        $bill->setTxnDate($entity->getTxnDate());

        if ($this->isMultiCurrencyEnabled()) {
            if (null !== $exchangeRate = $entity->getExchangeRate()) {
                $bill->set('ExchangeRate', $exchangeRate);
            }
        }
        $line1 = new QuickBooks_QBXML_Object_Bill_ExpenseLine();
        $line1->setAccountFullName($entity->getLine1AccountFullName());
        $line1->setAmount($this->getAmount($entity->getLine1Amount()));
        $line1->setMemo($entity->getLine1Memo());
        $bill->addExpenseLine($line1);

        if (!LineItemLogic::isValueEmpty($entity->getLine2AccountFullName())) {
            $line2 = new QuickBooks_QBXML_Object_Bill_ExpenseLine();
            $line2->setAccountFullName($entity->getLine2AccountFullName());
            $line2->setAmount($this->getAmount($entity->getLine2Amount()));
            $line2->setMemo($entity->getLine2Memo());
            $bill->addExpenseLine($line2);
        }

        $results = $this->vendorTransformer->transform($entity);
        $results[] = [QUICKBOOKS_ADD_BILL, $bill];
        return $results;
    }
}
