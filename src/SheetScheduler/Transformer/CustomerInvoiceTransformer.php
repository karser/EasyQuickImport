<?php declare(strict_types=1);

namespace App\SheetScheduler\Transformer;

use App\SheetScheduler\CustomerInvoice;
use App\SheetScheduler\EntityTransformerInterface;
use App\SheetScheduler\LineItemLogic;
use QuickBooks_QBXML_Object_Invoice;
use QuickBooks_QBXML_Object_Invoice_InvoiceLine;
use Webmozart\Assert\Assert;

class CustomerInvoiceTransformer implements EntityTransformerInterface
{
    use TransformerContextTrait;

    /** @var CustomerTransformer */
    private $customerTransformer;

    public function __construct(CustomerTransformer $customerTransformer)
    {
        $this->customerTransformer = $customerTransformer;
    }

    public function supports(string $class): bool
    {
        return $class === CustomerInvoice::class;
    }

    /**
     * @param CustomerInvoice|object $entity
     */
    public function transform($entity): array
    {
        Assert::isInstanceOf($entity, CustomerInvoice::class);
        $this->customerTransformer->setCompany($this->company);

        $Invoice = new QuickBooks_QBXML_Object_Invoice();

        $Invoice->setCustomerFullName($entity->getCustomerFullName());
        $Invoice->setRefNumber($entity->getRefNumber());
        $Invoice->setMemo($entity->getInvoiceMemo());
        $Invoice->setARAccountName($entity->getArAccount());
        $Invoice->setTermsName($entity->getTerms());

        $Invoice->setTxnDate($entity->getTxnDate());
        if ($this->isMultiCurrencyEnabled()) {
            $Invoice->set('ExchangeRate', $entity->getExchangeRate());
        }
        [$a1, $a2, $a3, $a4, $a5, $ct, $st, $pr, $zip, $cn] = $entity->composeBillAddress();
        $Invoice->setBillAddress($a1, $a2, $a3, $a4, $a5, $ct, $st, $pr, $zip, $cn);

        $InvoiceLine1 = new QuickBooks_QBXML_Object_Invoice_InvoiceLine();
        $InvoiceLine1->setItemName($entity->getLine1ItemName());
        $InvoiceLine1->setDesc($entity->getLine1Desc());
        $InvoiceLine1->setRate($this->getAmount($entity->getLine1Rate()));
        $InvoiceLine1->setAmount($this->getAmount($entity->getLine1Amount()));
        $quantity = LineItemLogic::getQuantity(
            $entity->getLine1Quantity(),
            $entity->getLine1Rate(),
            $entity->getLine1Amount()
        );
        $InvoiceLine1->setQuantity($quantity);
        $Invoice->addInvoiceLine($InvoiceLine1);

        $results = $this->customerTransformer->transform($entity);
        $results[] = [QUICKBOOKS_ADD_INVOICE, $Invoice];
        return $results;
    }
}
