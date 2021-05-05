<?php declare(strict_types=1);

namespace App\SheetScheduler\Transformer;

use App\SheetScheduler\Customer;
use App\SheetScheduler\EntityTransformerInterface;
use Webmozart\Assert\Assert;

class CustomerTransformer implements EntityTransformerInterface
{
    use TransformerContextTrait;

    public function supports(string $class): bool
    {
        return $class === Customer::class;
    }

    /**
     * @param Customer|object $entity
     */
    public function transform($entity): array
    {
        Assert::isInstanceOf($entity, Customer::class);

        $Customer = new \QuickBooks_QBXML_Object_Customer();

        $Customer->setFullName($entity->getCustomerFullName());
        $Customer->setFirstName($entity->getFirstName());
        $Customer->setLastName($entity->getLastName());
        $Customer->setCompanyName($entity->getCompanyName());
        $Customer->setTermsFullName($entity->getTerms());

        [$a1, $a2, $a3, $a4, $a5, $ct, $st, $pr, $zip, $cn] = $entity->composeBillAddress();
        $Customer->setBillAddress($a1, $a2, $a3, $a4, $a5, $ct, $st, $pr, $zip, $cn);

        if ($this->isMultiCurrencyEnabled()) {
            $currency = $entity->getCurrency();
            if ($currency !== null && $currency !== '') {
                $Customer->set('CurrencyRef FullName', $entity->getCurrency());
            }
        }
        return [
            [QUICKBOOKS_ADD_CUSTOMER, $Customer]
        ];
    }
}
