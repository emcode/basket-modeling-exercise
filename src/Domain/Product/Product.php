<?php declare(strict_types=1);

namespace Acme\Domain\Product;

use Acme\Domain\Product\Exception\InvalidProductDataException;

class Product
{
    public function __construct(
        protected string $code,
        protected int $price,
        protected string $name
    )
    {
        if (empty($this->code)) {
            throw new InvalidProductDataException("Product code cannot be empty");
        }

        if ($this->price < 0) {
            throw new InvalidProductDataException("Product price cannot be negative");
        }

        if (empty($name)) {
            throw new InvalidProductDataException("Product name cannot be empty");
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getName(): string
    {
        return $this->name;
    }
}