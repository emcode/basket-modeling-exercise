<?php declare(strict_types=1);

namespace Acme\Domain\Discount;

class DiscountMetadata
{
    public function __construct(
        protected string $discountId,
        protected ?int $discountedPrice,
    )
    {
    }

    public function getDiscountId(): string
    {
        return $this->discountId;
    }

    public function getDiscountedPrice(): ?int
    {
        return $this->discountedPrice;
    }

    public function hasAnyDiscountedPrice(): bool
    {
        return null !== $this->discountedPrice;
    }
}