<?php declare(strict_types=1);

namespace Acme\Domain\Discount;

use Acme\Domain\Basket\Item as BasketItem;

class DiscountApplicator
{
    /**
     * @param DiscountInterface[] $discounts
     */
    public function __construct(
        protected array $discounts = []
    )
    {
    }

    /**
     * @param BasketItem[] $basketItems
     */
    public function findBasketItemForProduct(array $basketItems, string $productCode): ?BasketItem
    {
        $matchingBasketItem = null;
        foreach($basketItems as $bi) {
            // this is not elegant
            // in the future change/move this fragment to depend on available discounts + existing basket data
            // and not necessarily / not only on basket item method invoked below
            // but let's leave it for now
            if ($bi->doesWantToReceiveAnotherProduct($productCode)) {
                $matchingBasketItem = $bi;
                break;
            }
        }
        return $matchingBasketItem;
    }

    public function findDiscountForProduct(string $productCode): ?DiscountInterface
    {
        $matchingOffer = null;
        foreach($this->discounts as $offer) {
            if ($offer->canBeAppliedToProduct($productCode)) {
                $matchingOffer = $offer;
                break;
            }
        }
        return $matchingOffer;
    }

}