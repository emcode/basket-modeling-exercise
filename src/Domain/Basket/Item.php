<?php declare(strict_types=1);

namespace Acme\Domain\Basket;

use Acme\Domain\Discount\DiscountInterface;
use Acme\Domain\Product\Product;

/**
 *  Basket Item
 *
 *  Products/Components sequentially added to the basket are grouped into Basket Items
 *  Algorithm of this grouping process depends on configured discounts
 *
 *  In short: if two Products/Components are in the same Basket Item it means that they are
 *  bundled together - probably because special offer (discount) was applied by DiscountApplicator
 */
class Item
{
    /**
     * @var array|Component[]
     */
    protected array $components = [];

    public function __construct(
        Product $product,
        protected ?DiscountInterface $discount = null
    )
    {
        $this->addProduct(
            $product
        );
    }

    public function addProduct(Product $product): void
    {
        $newComponent = new Component(
            $product->getCode(),
            $product->getPrice(),
            null
        );
        $newSetOfComponents = array_merge($this->components, [ $newComponent ]);
        if ($this->discount) {
            $newSetOfComponents = $this->discount->applyToBasketItemComponents($newSetOfComponents);
        }
        $this->components = $newSetOfComponents;
    }

    public function getTotal(): int {
        return array_reduce(
            $this->components,
            function (int $sum, Component $c) {
                $sum += $c->getEndCustomerPrice();
                return $sum;
            },
            0
        );
    }

    public function doesWantToReceiveAnotherProduct(string $productCode): bool
    {
        return $this->discount && $this->discount->canBeAppliedToProduct($productCode);
    }
}