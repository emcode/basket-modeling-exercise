<?php declare(strict_types=1);

namespace Acme\Domain\Discount;

use Acme\Domain\Basket\Component;

interface DiscountInterface
{
    public function getId(): string;
    public function canBeAppliedToProduct(string $productCode): bool;

    /**
     * @param Component[] $basketItemComponents
     * @return Component[]
     */
    public function applyToBasketItemComponents(array $basketItemComponents): array;
}