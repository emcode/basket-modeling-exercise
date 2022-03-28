<?php declare(strict_types=1);

namespace Acme\Domain\Discount;

use Acme\Domain\Basket\ProductInBasket;

interface DiscountInterface
{
    public function getId(): string;

    /**
     * @param ProductInBasket[] $products
     * @return ProductInBasket[]
     */
    public function determineAffectedProducts(array $products): array;
    public function canBeTriggeredByProduct(string $productCode): bool;

    /**
     * @param ProductInBasket[] $products
     * @return ProductInBasket[]
     */
    public function apply(array $products): array;
}