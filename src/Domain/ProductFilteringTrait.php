<?php

namespace Acme\Domain;

use Acme\Domain\Basket\ProductInBasket;
use Acme\Domain\Product\Product;
use Symfony\Component\Uid\Uuid;

trait ProductFilteringTrait
{
    /**
     * @param ProductInBasket[] $products
     * @param ProductInBasket[]
     */
    public function selectProductsByCode(array $products, string $code): array
    {
        return array_filter(
            $products,
            fn (Product $product) => $product->getCode() === $code
        );
    }

    public function uuidToString(Uuid $uuid): string
    {
        return $uuid->toRfc4122();
    }

    /**
     * @param ...$productLists
     * @return ProductInBasket[]
     */
    public function unifyProducts(... $productLists): array
    {
        $unifiedProducts = [];
        /* @var $currentList ProductInBasket[] */
        foreach($productLists as $currentList) {
            foreach($currentList as $currentProduct) {
                $unifiedProducts[$this->uuidToString($currentProduct->getUuid())] = $currentProduct;
            }
        }
        return array_values($unifiedProducts);
    }

    /**
     * @param ProductInBasket[] $products
     * @return string[]
     */
    public function extractUuidsAsString(array $products): array
    {
        return array_map(
            fn (ProductInBasket $p) => $this->uuidToString($p->getUuid()),
            $products
        );
    }

    /**
     * @param ...$productLists
     * @return ProductInBasket[]
     */
    public function diffProducts($reference, ... $productLists): array
    {
        $diffedProducts = [];
        $referenceUuids = $this->extractUuidsAsString($reference);

        foreach($productLists as $currentList) {
            foreach($currentList as $currentProduct) {
                $uidAsString = $this->uuidToString($currentProduct->getUuid());
                if (!in_array($uidAsString, $referenceUuids, true)) {
                    $diffedProducts[$uidAsString] = $currentProduct;
                }
            }
        }

        return array_values($diffedProducts);
    }
}