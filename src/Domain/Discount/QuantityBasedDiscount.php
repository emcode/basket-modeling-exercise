<?php declare(strict_types=1);

namespace Acme\Domain\Discount;

use Acme\Domain\Basket\ProductInBasket;
use Acme\Domain\ProductFilteringTrait;

class QuantityBasedDiscount implements DiscountInterface
{
    use ProductFilteringTrait;

    public function __construct(
        protected string $id,
        // by which product code this discount can be triggered?
        protected string $triggeringProductCode,
        // which product code should be discounted when discount is triggered?
        protected string $affectedProductCode,
        // how much do we want to lower the initial price of a product?
        protected int $discountPercentage,
        // how many products within basket item has to exist for this discount to be applied?
        protected int $requiredQuantity,
        // to how many products within basket item discount should be applied?
        protected int $applyToQuantity,
    )
    {
        if (empty($this->triggeringProductCode)) {
            throw new \InvalidArgumentException("Triggering product code cannot be empty");
        }

        if (empty($this->affectedProductCode)) {
            throw new \InvalidArgumentException("Affected product code cannot be empty");
        }

        if ($this->discountPercentage <= 0 || $this->discountPercentage > 100) {
            throw new \InvalidArgumentException(
                "Discount percentage should be > 0 and <= 100. Received: %s",
                $this->discountPercentage
            );
        }

        if ($this->requiredQuantity <= 0) {
            throw new \InvalidArgumentException(
                "Required quantity arg has to be > 0 Received: %s",
                $this->requiredQuantity
            );
        }

        if ($this->applyToQuantity <= 0) {
            throw new \InvalidArgumentException(
                "Apply to quantity arg has to be > 0 Received: %s",
                $this->requiredQuantity
            );
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function determineAffectedProducts(array $products): array
    {
        $triggeringProducts = $this->selectTriggeringProducts($products);
        if (count($triggeringProducts) < $this->requiredQuantity) {
            return [];
        }
        $discountedProducts = $this->selectDiscountedProducts($products);
        if (empty($discountedProducts)) {
            return [];
        }
        return $this->unifyProducts($triggeringProducts, $discountedProducts);
    }

    public function selectTriggeringProducts(array $products): array
    {
        $allTriggeringProducts = $this->selectProductsByCode(
            $products,
            $this->triggeringProductCode
        );

        return array_slice($allTriggeringProducts, 0, $this->requiredQuantity);
    }

    public function selectDiscountedProducts(array $products): array
    {
        $productsToBeDiscounted = $this->selectProductsByCode(
            $products,
            $this->affectedProductCode
        );

        return array_slice($productsToBeDiscounted, 0, $this->applyToQuantity);
    }
    
    public function canBeTriggeredByProduct(string $productCode): bool
    {
        return $this->triggeringProductCode === $productCode;
    }

    /**
     * @param ProductInBasket[] $products
     * @return ProductInBasket[]
     */
    public function apply(array $products): array
    {
        $triggeringProducts = array_map(
            fn (ProductInBasket $p) => ProductInBasket::createFromProduct(
                $p,
                $p->getUuid(),
                $this->id,
               null
            ),
            $this->selectTriggeringProducts($products)
        );

        $discountedProducts = array_map(
            fn (ProductInBasket $p) => ProductInBasket::createFromProduct(
                $p,
                $p->getUuid(),
                $this->id,
                $this->calculateDiscountedPrice($p->getPrice())
            ),
            $this->selectDiscountedProducts($products)
        );

        return $this->unifyProducts($triggeringProducts, $discountedProducts);
    }

    public function calculateDiscountedPrice(int $initialPrice): int
    {
        // we use bcmath function here to not have to think about rounding
        // errors / floating point issues when working with price values
        $percentage = bcdiv((string) $this->discountPercentage, '100', 2);
        return (int) bcmul((string) $initialPrice, $percentage, 0);
    }
}