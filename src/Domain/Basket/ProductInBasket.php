<?php declare(strict_types=1);

namespace Acme\Domain\Basket;

use Acme\Domain\Discount\DiscountMetadata;
use Acme\Domain\Product\Product;
use Symfony\Component\Uid\Uuid;

class ProductInBasket extends Product
{
    public function __construct(
        protected Uuid $uuid,
        string $code,
        int $originalPrice,
        string $name,
        protected ?DiscountMetadata $discount
    )
    {
        parent::__construct($code, $originalPrice, $name);

        if (
            $this->hasAnyDiscountedPrice() &&
            $this->getDiscountedPrice() >= $originalPrice)
        {
            throw new \InvalidArgumentException(sprintf(
                "Discounted price cannot be >= than original price (%s, %s)",
                $this->getDiscountedPrice(), $originalPrice
            ));
        }
    }

    public function hasAnyDiscountedPrice(): bool
    {
        return $this->discount && $this->discount->hasAnyDiscountedPrice();
    }

    public function getDiscountedPrice(): ?int
    {
        return $this->discount?->getDiscountedPrice();
    }

    public function getEndCustomerPrice(): int
    {
        return $this->hasAnyDiscountedPrice()
            ? $this->getDiscountedPrice()
            : $this->price
        ;
    }

    /**
     * @return Uuid
     */
    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public static function createFromProduct(
        Product $p,
        Uuid $uuid,
        ?string $discountId,
        ?int $discountedPrice
    ): ProductInBasket {
        return new ProductInBasket(
            $uuid,
            $p->getCode(),
            $p->getPrice(),
            $p->getName(),
            $discountId
              ? new DiscountMetadata($discountId, $discountedPrice)
              : null
        );
    }
}