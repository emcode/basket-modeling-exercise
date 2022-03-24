<?php declare(strict_types=1);

namespace Acme\Domain\Discount;

use Acme\Domain\Basket\Component;

class QuantityBasedDiscount implements DiscountInterface
{
    public function __construct(
        protected string $id,
        // for which product code this special offer "reacts"?
        protected string $productCode,
        // how much do we want to lower the initial price of a product?
        protected int $discountPercentage,
        // how many products within basket item has to exist for this discount to be applied?
        protected int $requiredQuantity,
        // to how many products within basket item discount should be applied?
        protected int $applyToQuantity,
    )
    {
        if (empty($this->productCode)) {
            throw new \InvalidArgumentException("Product code cannot be empty");
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

    public function canBeAppliedToProduct(string $productCode): bool
    {
        return $this->productCode === $productCode;
    }

    /**
     * It returns new array with processed basket items, leaving original array untouched
     * It copies all objects within array too, to ensure immutability of original values
     *
     * @param Component[] $basketItemComponents
     * @return Component[]
     */
    public function applyToBasketItemComponents(array $basketItemComponents): array
    {
        $this->assertProductCodeCorrectness($basketItemComponents);

        if ($this->requiredQuantity <= count($basketItemComponents)) {
            $componentsToDiscount = array_slice($basketItemComponents, 0, $this->applyToQuantity);
            $componentsToLeaveAlone = array_slice($basketItemComponents, count($componentsToDiscount));
        } else {
            $componentsToDiscount = [];
            $componentsToLeaveAlone = $basketItemComponents;
        }

        $newComponents = [];

        foreach($componentsToDiscount as $c) {
            $newComponents[] = $this->applyToComponent($c);
        }

        foreach($componentsToLeaveAlone as $c) {
            $newComponents[] = $c->createCopyWithoutDiscountedPrice();
        }

        return $newComponents;
    }

    protected function applyToComponent(Component $c): Component
    {
        // we use bcmath function here to not have to think about rounding
        // errors / floating point issues when working with price values
        $percentage = bcdiv((string) $this->discountPercentage, '100', 2);
        $discounted = (int) bcmul((string) $c->getInitialPrice(), $percentage, 0);
        return $c->createCopyWithNewDiscountedPrice($discounted);
    }

    /**
     * @param array|Component[] $basketItemComponents
     */
    protected function assertProductCodeCorrectness(array $basketItemComponents): void
    {
        foreach($basketItemComponents as $c) {
            if (!($this->canBeAppliedToProduct($c->getProductCode()))) {
                throw new \RuntimeException(sprintf(
                    "Offer with id %s cannot be applied to product with code %s. Sth went seriously wrong at earlier stage!",
                    $this->id, $c->getProductCode()
                ));
            }
        }
    }
}