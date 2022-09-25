<?php
declare(strict_types=1);

namespace Siru\PayumSiru\Tests;

use PHPUnit\Framework\TestCase;
use Siru\PayumSiru\PriceHelper;

/**
 * @covers \Siru\PayumSiru\PriceHelper
 */
class PriceHelperTest extends TestCase
{

    /**
     * @test
     * @dataProvider pricesProvider
     */
    public function formatsPrice(int $price, string $expected) : void
    {
        $this->assertEquals($expected, PriceHelper::formatPrice($price));
    }

    /**
     * @test
     * @dataProvider taxProvider
     */
    public function deductsVat(string $price, int $taxClass, string $expected) : void
    {
        $this->assertEquals($expected, PriceHelper::calculatePriceWithoutVat($price,  $taxClass));
    }

    /**
     * @return array<array{int, string}>
     */
    public function pricesProvider() : array
    {
        return [
            [1, '0.01'],
            [9, '0.09'],
            [19, '0.19'],
            [190, '1.90'],
            [19191919, '191919.19'],
        ];
    }

    /**
     * @return array<array{string, int, string}>
     */
    public function taxProvider() : array
    {
        return [
            ['1.24', 3, '1.00'],
            ['1.14', 2, '1.00'],
            ['1.10', 1, '1.00'],
            ['1.00', 0, '1.00'],
        ];
    }

}