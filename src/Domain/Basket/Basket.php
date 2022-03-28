<?php declare(strict_types=1);

namespace Acme\Domain\Basket;

use Acme\Domain\Delivery\ChargeResolver as DeliveryChargeResolver;
use Acme\Domain\Discount\DiscountApplicator;
use Acme\Domain\Discount\DiscountInterface;
use Acme\Domain\Product\CatalogInterface as ProductCatalogInterface;
use Acme\Domain\ProductFilteringTrait;
use Symfony\Component\Uid\Uuid;

class Basket
{
    use ProductFilteringTrait;

    /**
     * @var ProductInBasket[]
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
        $basketProduct = ProductInBasket::createFromProduct(
            $this->productCatalog->getProductByCode($code),
            Uuid::v4(),
            null,
            null
        );
        $updatedItemsWithoutDiscounts = array_merge(
            $this->cleanupAnyDiscountedPricing($this->items),
            [ $basketProduct ]
        );
        $this->items = $this->calculateDiscountedPricing(
            $this->discountApplicator->distributeDiscountsToProducts(
                $updatedItemsWithoutDiscounts
            ),
            $updatedItemsWithoutDiscounts
        );
    }

    /**
     * @param ProductInBasket[] $products
     * @return ProductInBasket[]
     */
    public function cleanupAnyDiscountedPricing(array $products): array
    {
        return array_map(
            fn (ProductInBasket $p) => ProductInBasket::createFromProduct($p, $p->getUuid(), null, null),
            $products
        );
    }

    /**
     * @param array $discountData
     * @param ProductInBasket[] $allProducts
     * @return ProductInBasket[]
     */
    public function calculateDiscountedPricing(array $discountData, array $allProducts): array
    {
        return $this->unifyProducts(
            $allProducts,
            $this->applyDiscountsToProducts($discountData)
        );
    }

    /**
     * @param ProductInBasket[] $discountDistribution
     * @return ProductInBasket[]
     */
    public function applyDiscountsToProducts(array $discountDistribution): array {
        $productsWithAppliedDiscounts = [];
        /* @var $discount DiscountInterface */
        /* @var $affectedProducts ProductInBasket[] */
        foreach($discountDistribution as [ $discount, $affectedProducts ]) {
            $productsWithAppliedDiscounts = array_merge(
                $productsWithAppliedDiscounts,
                $discount->apply($affectedProducts)
            );
        }
        return $productsWithAppliedDiscounts;
    }

    protected function getBasketItemsTotal(): int {
        $sum = 0;
        foreach ($this->items as $product) {
            $sum += $product->getEndCustomerPrice();
        }
        return $sum;
    }

    public function getTotal(): int {
        $productsPrice = $this->getBasketItemsTotal();
        $deliveryPrice = $this->deliveryChargeResolver->resolveDeliveryCost($productsPrice);
        return $productsPrice + $deliveryPrice;
    }
}