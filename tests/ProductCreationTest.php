<?php

namespace Tests\Acme;

use Acme\Domain\Product\Exception\InvalidProductDataException;
use Acme\Domain\Product\Product;
use PHPUnit\Framework\TestCase;

class ProductCreationTest extends TestCase
{
    public function provideInvalidProductData(): array
    {
        return [
            [ "", 9999, "Cool Product" ],
            [ "R01", -3295, "Red Widget" ],
            [ "G01", 2495, "" ],
            [ "", 0, "" ],
        ];
    }

    /**
     * @dataProvider provideInvalidProductData
     */
    public function testCannotCreateProductWithInvalidData(string $code, int $price, string $name): void
    {
        $this->expectException(InvalidProductDataException::class);
        new Product($code, $price, $name);
    }

    public function provideValidProductData(): array
    {
        return [
            [ "XYZ", 9999, "Cool Product" ],
            [ "FREE", 0, "Free Product" ],
            [ "R01", 3295, "Red Widget" ],
            [ "G01", 2495, "Green Widget" ],
            [ "B01", 795, "Blue Widget" ],
        ];
    }
    
    /**
     * @dataProvider provideValidProductData
     */
    public function testCanCreateProductWithValidData(string $code, int $price, string $name): void
    {
        $p = new Product($code, $price, $name);
        $this->assertInstanceOf(Product::class, $p);
    }
}