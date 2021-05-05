<?php declare(strict_types=1);

namespace App\SheetScheduler\Transformer;

use App\SheetScheduler\EntityTransformerInterface;
use App\SheetScheduler\Vendor;
use QuickBooks_QBXML_Object_Vendor;
use Webmozart\Assert\Assert;

class VendorTransformer implements EntityTransformerInterface
{
    use TransformerContextTrait;

    public function supports(string $class): bool
    {
        return $class === Vendor::class;
    }

    /**
     * @param Vendor|object $entity
     */
    public function transform($entity): array
    {
        Assert::isInstanceOf($entity, Vendor::class);
        $vendor = new QuickBooks_QBXML_Object_Vendor();

        $vendor->setName($entity->getVendorFullname());
        $vendor->setCompanyName($entity->getVendorCompanyName());

        $vendor->setVendorAddress($entity->getAddr1(), $entity->getAddr2(), '', '', '',
            $entity->getCity(), $entity->getState(), $entity->getPostalcode(), $entity->getCountry());

        $vendor->setVendorTypeRef($entity->getVendorType());
        $terms = $entity->getTerms();
        if ($terms !== null) {
            $vendor->set('TermsRef FullName', $terms);
        }
        if ($this->isMultiCurrencyEnabled()) {
            $currency = $entity->getCurrency();
            if ($currency !== null) {
                $vendor->set('CurrencyRef FullName', $currency);
            }
        }
        return [
            [QUICKBOOKS_ADD_VENDOR, $vendor]
        ];
    }
}
