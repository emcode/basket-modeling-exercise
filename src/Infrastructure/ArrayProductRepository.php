<?php declare(strict_types=1);

namespace Acme\Infrastructure;

use Acme\Domain\Product\CatalogInterface;
use Acme\Domain\Product\Exception\ProductDoesNotExistException;
use Acme\Domain\Product\Product;

class ArrayProductRepository implements CatalogInterface
{
    protected array $productsByCode;

    public function __construct(
        array $products
    )
    {
        $this->productsByCode = array_reduce(
            $products,
            function(array $carry, Product $p) {
                $carry[$p->getCode()] = $p;
                return $carry;
            },
            []
        );
    }

    public function getProductByCode(string $code): Product
    {
        if (!isset($this->productsByCode[$code])) {
            throw new ProductDoesNotExistException("Product with provided code ($code) does not exists!");
        }

        return $this->productsByCode[$code];
    }
}