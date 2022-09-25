<?php
declare(strict_types=1);

namespace Siru\PayumSiru;

class PriceHelper
{

    /**
     * Siru API expects prices always with two decimals and dot decimal separator.
     */
    public static function formatPrice(int|float $amount) : string
    {
        $basePrice = $amount / 100;
        return number_format($basePrice, 2, '.', '');
    }

    /**
     * Remove VAT from price.
     */
    public static function calculatePriceWithoutVat(string $amount, int $taxClass) : string
    {
        if ($taxClass < 0 || $taxClass > 3) {
            throw new \InvalidArgumentException('Argument $taxClass must be an integer between 0 and 3.');
        }
        if (0 === $taxClass) {
            return $amount;
        }
        $intVal = (int) str_replace('.', '', $amount);
        $taxPercentage = match ($taxClass) {
            1 => 1.10,
            2 => 1.14,
            3 => 1.24
        };

        $basePrice = $intVal / $taxPercentage;
        return self::formatPrice($basePrice);
    }

}