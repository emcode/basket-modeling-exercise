<?php declare(strict_types=1);

namespace Acme\Domain\Discount;

use Acme\Domain\Basket\ProductInBasket;
use Acme\Domain\ProductFilteringTrait;

class DiscountApplicator
{
    use ProductFilteringTrait;

    /**
     * @param DiscountInterface[] $discounts
     */
    public function __construct(
        protected array $discounts = []
    )
    {
    }

    /**
     * @param ProductInBasket[] $products
     * @param array
     */
    public function distributeDiscountsToProducts(array $products): array
    {
        $results = [];
        foreach($products as $product) {
            [ $discount, $affectedProducts, $unaffectedProducts ] = $this->matchDiscountToProduct(
                $product->getCode(),
                $products
            );
            if (null === $discount || empty($affectedProducts)) {
                continue;
            }
            $results[] = [ $discount, $affectedProducts ];
            $results = array_merge(
                $results,
                $this->distributeDiscountsToProducts($unaffectedProducts)
            );
        }
        return $results;
    }

    /**
     * @param ProductInBasket[] $productsToAnalyse
     */
    public function matchDiscountToProduct(string $triggeringProductCode, array $productsToAnalyse): ?array
    {
        $matchedDiscount = null;
        $affectedProducts = null;
        $unaffectedProducts = null;

        foreach($this->getDiscountCandidatesForProductCode($triggeringProductCode) as $discountCandidate) {
            [ $affectedProducts, $unaffectedProducts ] = $this->findOutAffectedAndUnaffectedProducts(
                $discountCandidate,
                $productsToAnalyse
            );
            if (empty($affectedProducts)) {
                continue;
            }
            $matchedDiscount = $discountCandidate;
            break;
        }

        return $matchedDiscount
            ? [ $matchedDiscount, $affectedProducts, $unaffectedProducts ]
            : [ null, [], [] ]
        ;
    }

    /**
     * @param ProductInBasket[] $productsToAnalyse
     */
    public function findOutAffectedAndUnaffectedProducts(DiscountInterface $discount, array $productsToAnalyse): array
    {
        $affectedProducts = $discount->determineAffectedProducts($productsToAnalyse);
        $unaffectedProducts = $this->diffProducts($affectedProducts, $productsToAnalyse);
        return [
            $affectedProducts,
            $unaffectedProducts
        ];
    }

    /**
     * @return DiscountInterface[]
     */
    protected function getDiscountCandidatesForProductCode(string $productCode): array
    {
        return array_filter(
            $this->discounts,
            fn (DiscountInterface $d) => $d->canBeTriggeredByProduct($productCode)
        );
    }
}