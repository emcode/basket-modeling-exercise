<?php declare(strict_types=1);

namespace Acme\Domain\Product;

interface CatalogInterface
{
    public function getProductByCode(string $code): Product;
}