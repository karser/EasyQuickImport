<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Exception\RuntimeException;

class LineItemLogic
{
    /**
     * @param mixed $quantity
     * @param mixed $rate
     * @param mixed $amount
     * @return string
     */
    public static function getQuantity($quantity, $rate, $amount): string
    {
        if (!self::isValueEmpty($quantity)) {
            return $quantity;
        }
        if (self::isValueEmpty($amount)) {
            throw new RuntimeException('Field Quantity or Amount is required');
        }
        if (self::isValueEmpty($rate)) {
            $rate = $amount;
        }
        return number_format($amount / $rate, 4, '.', '');
    }

    public static function isValueEmpty(?string $quantity): bool
    {
        return null === $quantity || '' === trim($quantity);
    }
}
