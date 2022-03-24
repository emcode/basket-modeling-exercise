<?php declare(strict_types=1);

namespace Acme\Domain\Delivery;

use Acme\Domain\Delivery\Exception\CouldNotResolveDeliveryCostException;

class ChargeResolver
{
    /**
     * Important:
     * we assume that criteria passed to constructor are ordered in correct / desired way
     * the first matched criteria is chosen as active one (the rest are ignored)
     *
     * @param ChargeCriteria[] $orderedCriteria
     */
    public function __construct(
        protected array $orderedCriteria
    )
    {
    }

    public function resolveDeliveryCost(int $totalForBasketItems): int
    {
        $deliveryCost = null;

        foreach($this->orderedCriteria as $criterion) {
            if ($criterion->isMatching($totalForBasketItems)) {
                $deliveryCost = $criterion->getDeliveryCost();
                break;
            }
        }

        if (null === $deliveryCost) {
            throw new CouldNotResolveDeliveryCostException(
                sprintf(
                    "Based on currently configured criteria (checked %s of them) we could 
                    not determine delivery cost for value: %s",
                    count($this->orderedCriteria), $totalForBasketItems
                )
            );
        }

        return $deliveryCost;
    }
}