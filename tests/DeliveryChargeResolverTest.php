<?php

namespace Tests\Acme;

use Acme\Domain\Delivery\ChargeCriteria;
use Acme\Domain\Delivery\ChargeResolver as DeliveryChargeResolver;
use Acme\Domain\Delivery\Exception\CouldNotResolveDeliveryCostException;
use Acme\Domain\Operator;
use PHPUnit\Framework\TestCase;

class DeliveryChargeResolverTest extends TestCase
{
    public function provideExampleData(): array
    {
        $criteriaSet01 = [
            new ChargeCriteria(Operator::LESS_THAN, 10, 50),
            new ChargeCriteria(Operator::LESS_THAN, 30, 100),
            new ChargeCriteria(Operator::GREATER_OR_EQUAL_THAN, 30, 200),
        ];

        $criteriaSet02 = [
            new ChargeCriteria(Operator::LESS_THAN, 5000, 495),
            new ChargeCriteria(Operator::LESS_THAN, 9000, 295),
            new ChargeCriteria(Operator::GREATER_OR_EQUAL_THAN, 9000, 0),
        ];

        return [
            [ 40, $criteriaSet01, 200 ],
            [ 30, $criteriaSet01, 200 ],
            [ 29, $criteriaSet01, 100 ],
            [ 10, $criteriaSet01, 100 ],
            [ 9,  $criteriaSet01, 50 ],
            [ 0,  $criteriaSet01, 50 ],
            [ 0,    $criteriaSet02, 495 ],
            [ 1000, $criteriaSet02, 495 ],
            [ 4999, $criteriaSet02, 495 ],
            [ 8000, $criteriaSet02, 295 ],
            [ 8999, $criteriaSet02, 295 ],
            [ 9000, $criteriaSet02, 0 ],
            [ 9001, $criteriaSet02, 0 ],
        ];
    }

    /**
     * @dataProvider provideExampleData
     */
    public function testDeliveryCostIsCalculatedAsExpected(int $itemsTotal, array $chargeCriteria, int $expectedCost): void
    {
        $resolver = new DeliveryChargeResolver($chargeCriteria);
        $this->assertEquals($expectedCost, $resolver->resolveDeliveryCost($itemsTotal));
    }

    public function testThrowsOnIncoherentCriteria(): void
    {
        $resolver = new DeliveryChargeResolver([
            new ChargeCriteria(Operator::LESS_OR_EQUAL_THAN, 20, 200),
            new ChargeCriteria(Operator::GREATER_OR_EQUAL_THAN, 30, 300)
        ]);
        $this->expectException(CouldNotResolveDeliveryCostException::class);
        $resolver->resolveDeliveryCost(25);
    }
}