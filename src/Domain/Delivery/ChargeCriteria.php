<?php declare(strict_types=1);

namespace Acme\Domain\Delivery;

use Acme\Domain\Delivery\Exception\InvalidChargeCriteriaDataException;
use Acme\Domain\Operator;

class ChargeCriteria
{
    public function __construct(
        protected Operator $operator,
        protected int $basketItemsPriceThreshold,
        protected int $deliveryCost
    )
    {
        if ($basketItemsPriceThreshold < 0) {
            throw new InvalidChargeCriteriaDataException("Basket items price threshold cannot be negative");
        }

        if ($deliveryCost < 0) {
            throw new InvalidChargeCriteriaDataException("Delivery cost cannot be negative");
        }
    }

    public function isMatching(int $val): bool
    {
        return match($this->operator) {
            Operator::LESS_THAN => ($val < $this->basketItemsPriceThreshold),
            Operator::LESS_OR_EQUAL_THAN => ($val <= $this->basketItemsPriceThreshold),
            Operator::GREATER_THAN => ($val > $this->basketItemsPriceThreshold),
            Operator::GREATER_OR_EQUAL_THAN => ($val >= $this->basketItemsPriceThreshold),
        };
    }

    public function getDeliveryCost(): int
    {
        return $this->deliveryCost;
    }
}