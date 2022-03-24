<?php declare(strict_types=1);

namespace Acme\Domain\Basket;

use Acme\Domain\Basket\Item as BasketItem;
use Acme\Domain\Delivery\ChargeResolver as DeliveryChargeResolver;
use Acme\Domain\Discount\DiscountApplicator;
use Acme\Domain\Product\CatalogInterface as ProductCatalogInterface;

class Basket
{
    /**
     * @var BasketItem[]
     */
    protected array $items = [];

    public function __construct(
        protected ProductCatalogInterface $productCatalog,
        protected DiscountApplicator      $discountApplicator,
        protected DeliveryChargeResolver  $deliveryChargeResolver
    )
    {
    }

    public function addProduct(string $code): void {
        $product = $this->productCatalog->getProductByCode($code);
        $basketItem = $this->discountApplicator->findBasketItemForProduct(
            $this->items,
            $product->getCode()
        );
        if ($basketItem) {
            $basketItem->addProduct($product);
        } else {
            $this->items[] = new BasketItem(
                $product,
                $this->discountApplicator->findDiscountForProduct($product->getCode())
            );
        }
    }

    protected function getBasketItemsTotal(): int {
        $sum = 0;
        foreach ($this->items as $item) {
            $sum += $item->getTotal();
        }
        return $sum;
    }

    public function getTotal(): int {
        $productsPrice = $this->getBasketItemsTotal();
        $deliveryPrice = $this->deliveryChargeResolver->resolveDeliveryCost($productsPrice);
        return $productsPrice + $deliveryPrice;
    }
}