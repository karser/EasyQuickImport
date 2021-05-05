<?php declare(strict_types=1);

namespace App\SheetScheduler\Transformer;

use App\Entity\QuickbooksCompany;

trait TransformerContextTrait
{
    /** @var QuickbooksCompany|null */
    private $company;

    public function setCompany(?QuickbooksCompany $company): void
    {
        $this->company = $company;
    }

    public function isMultiCurrencyEnabled(): bool
    {
        return null !== $this->company? $this->company->isMultiCurrencyEnabled() : false;
    }

    /**
     * Cast to decimal symbol still doesn't work
     * because of sprintf('%01.2f', (float) $amount)
     * See setAmountType/getAmountType in \QuickBooks_QBXML_Object
     */
    public function getAmount(?string $amount): ?string
    {
        if (null === $amount) {
            return null;
        }
        $amount = preg_replace('/[^0-9.-]/', '', $amount);

        if (is_string($amount) &&
            null !== $this->company &&
            null !== ($symbol = $this->company->getDecimalSymbol()) &&
            $symbol !== '.'
        ) {
            $amount = str_replace('.', $symbol, $amount);
        }
        return $amount;
    }
}
