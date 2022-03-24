<?php declare(strict_types=1);

namespace Acme\Domain\Basket;

/**
 * Basket Item Component
 *
 * This object essentially represents a product but in context shopping basket: it means that here
 * product can have optionally a different, discounted price
 */
class Component
{
    public function __construct(
        protected string $productCode,
        protected int $initialPrice,
        protected ?int $discountedPrice
    )
    {
        if (null !== $discountedPrice && $discountedPrice < 0) {
            throw new \InvalidArgumentException(sprintf(
                "Discounted price cannot be negative! Received: %s",
                $discountedPrice
            ));
        }

        if ($initialPrice < 0) {
            throw new \InvalidArgumentException(sprintf(
                "Initial price cannot be negative! Received: %s",
                $initialPrice
            ));
        }

        if (null !== $discountedPrice && $discountedPrice >= $initialPrice) {
            throw new \InvalidArgumentException(sprintf(
                "Discounted price cannot >= from initial price. Received: %s, %s",
                $discountedPrice, $initialPrice
            ));
        }

        if (empty($this->productCode)) {
            throw new \InvalidArgumentException(
                "Product code cannot be empty"
            );
        }
    }

    public function getInitialPrice(): int
    {
        return $this->initialPrice;
    }

    /**
     * End customer price is price that will be paid for this component
     * It is either:
     * - discounted price - if it is set (other than null)
     * - or initial product price
     */
    public function getEndCustomerPrice(): int
    {
        if (null !== $this->discountedPrice) {
            return $this->discountedPrice;
        }
        return $this->initialPrice;
    }

    public function getProductCode(): string
    {
        return $this->productCode;
    }

    public function createCopyWithoutDiscountedPrice(): Component
    {
        return new Component(
            $this->productCode,
            $this->initialPrice,
            null
        );
    }

    public function createCopyWithNewDiscountedPrice(int $discountedPrice): Component
    {
        return new Component(
            $this->productCode,
            $this->initialPrice,
            $discountedPrice
        );
    }
}