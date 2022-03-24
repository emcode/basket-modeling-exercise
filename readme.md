## Basket modeling exercise

This is experiment in modeling specific problem space / domain - in this case: shopping basket with 
configurable discounts and delivery charge rules. The imaginary company's name is: `Acme`.

## Assumptions

- I assume that there is no need for a method `Basket::addProduct($productCode, $quantity)`; we are always adding one
  product at a time - I assume that it is specific for Acme's products
- prices are given in cents not dollars (to avoid issues with floating points / rounding when dealing with money)
- conversion from and to cents should be done in UI layer (out of the scope of this library)
- order of delivery rules given in configuration matters (first match wins) and configuration has to be coherent;
  ideally should be validated beforehand
- configuration should be provided by instantiating PHP objects - as described in `Example of configuration...` section

## How to test it locally

Make sure you have `PHP 8.1` with `bcmath` extension (should be installed and enabled by default)
and `composer` installed. Then:
```
composer install
./vendor/bin/phpunit ./tests
```

The best example of overall usage of this library is in the `./tests/OverallSystemTest.php` file. It contains
values from original instruction to test if calculated results are correct.

## Roadmap / TODO

1. Generalize discount system, add another discount type to improve design (it wasn't in requirements, but it would be a great idea)
2. Improve procedure of adding items to the basket and interacting with discounts - it isn't "elegant" yet
4. Add separate unit tests for DiscountApplicator class
5. Add suggestion / infra for serialization / deserialization of the internal state of the basket (provide convenient DTO object)
6. Add suggestion / infra for serialization / deserialization of the configuration of the system

## Example of configuration and usage in application

```php

// 0. Import required classes:
use Acme\Infrastructure\ArrayProductRepository;
use Acme\Domain\Discount\DiscountApplicator;
use Acme\Domain\Discount\QuantityBasedDiscount;
use Acme\Domain\Delivery\ChargeResolver as DeliveryChargeResolver;
use Acme\Domain\Delivery\ChargeCriteria as DeliveryChargeCriteria;
use Acme\Domain\Product\Product;
use Acme\Domain\Basket\Basket;

// 1. Configure product catalog / repository
$productRepository = new ArrayProductRepository([
    new Product("A1", 1002, "Product A1"), // 10.02 USD
    new Product("B1", 4500, "Product B1"), // 45.00 USD
    new Product("C1", 6559, "Product C1"), // 65.59 USD
]);

// 2. Configure discount applicator
$discountApplicator = new DiscountApplicator([
    new QuantityBasedDiscount(
      'DISCOUNT-01', // unique id of a discount algorithm
      'C1', // targeted product code
       50, // percentage of a discount 0 -> 100
       2, // how many products with given code have to be added to the basket to activate this discount
       1, // how many products with given code should be affected by discount
   )
]);

// 3. Configure delivery cost resolver
$deliveryCostResolver = new DeliveryChargeResolver([
    new DeliveryChargeCriteria(
        Operator::LESS_THAN, 
        5000, // threshold - overall value of products added to the basket
        495 // delivery cost
    ),
    new DeliveryChargeCriteria(
        Operator::GREATER_OR_EQUAL_THAN,
        5000, 
        0
    ),
]);

// 4. Construct and use basket
$basket = new Basket(
  $productCatalog,
  $discountApplicator,
  $deliveryCostResolver
);

$basket->addProduct('A1');
$basket->addProduct('A1');
$basket->addProduct('B1');
$basket->addProduct('C1');

// total contains overall price of products with applicable discounts + delivery cost
// resulted value is in cents so remember to convert to dollars, before displaying
$total = $basket->getTotal(); 
```

## The end

This is not a real project. This is just an experiment.
