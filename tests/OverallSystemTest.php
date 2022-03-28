<?php

namespace Tests\Acme;

use Acme\Domain\Basket\Basket;
use Acme\Domain\Delivery\ChargeCriteria;
use Acme\Domain\Delivery\ChargeResolver;
use Acme\Domain\Discount\DiscountApplicator;
use Acme\Domain\Discount\QuantityBasedDiscount;
use Acme\Domain\Operator;
use Acme\Domain\Product\Product;
use Acme\Infrastructure\ArrayProductRepository;
use PHPUnit\Framework\TestCase;

class OverallSystemTest extends TestCase
{
    protected Basket $basket;

    protected function setUp(): void
    {
        parent::setUp();

        $productCatalog = new ArrayProductRepository([
            new Product('R01', 3295, "Red Widget"),
            new Product('G01', 2495, "Green Widget"),
            new Product('B01', 795, "Blue Widget"),
        ]);

        $discountApplicator = new DiscountApplicator([
            new QuantityBasedDiscount(
                'DQ-01',
                'R01',
                'R01',
                50,
                2,
                1
            )
        ]);

        $deliveryCostResolver = new ChargeResolver([
            new ChargeCriteria(
                Operator::LESS_THAN, 5000, 495
            ),
            new ChargeCriteria(
                Operator::LESS_THAN, 9000, 295
            ),
            new ChargeCriteria(
                Operator::GREATER_OR_EQUAL_THAN, 9000, 0
            ),
        ]);

        $this->basket = new Basket(
            $productCatalog,
            $discountApplicator,
            $deliveryCostResolver
        );
    }

    public function provideProductSetsWithExpectedTotal(): array {
        return [
            [ [ 'B01', 'G01' ], 3785 ],
            [ [ 'R01', 'R01' ], 5437 ],
            [ [ 'R01', 'G01' ], 6085 ],
            [ [ 'B01', 'B01', 'R01', 'R01', 'R01' ], 9827 ],
        ];
    }

    /**
     * @dataProvider provideProductSetsWithExpectedTotal
     */
    public function testTotalIsCalculatedCorrectlyWithDiscountsAndDelivery(
        array $productCodes,
        int $expectedTotal
    ): void
    {
        foreach($productCodes as $pc) {
            $this->basket->addProduct($pc);
        }
        $this->assertEquals($expectedTotal, $this->basket->getTotal());
    }
}