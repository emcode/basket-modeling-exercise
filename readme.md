## Basket modeling exercise

This is experiment in modeling specific problem space / domain - in this case: shopping basket with 
configurable discounts and delivery charge rules. The imaginary company's name is: `Acme`.

## Basic idea of how it works

Product:
- has unique product code and initial price
- is represented by instance of a `Product` class

Basket:
- contains list of products (instance of `ProductInBasket `that inherits from `Product`)
- products are added to the basket one at a time
- if you'd add product with code `A-01` twice, it will be represented as two separate `ProductInBasket` instances (and
  each of then will have same product code: `A-01`)

Discount:
- can be triggered by adding products to the `Basket` (products with specific: codes and quantity)
- can influence the price of products with specific product codes
- product(s) that trigger given discount can be the same as discounted products or can be different - it is configurable

## Assumptions

I assume that:
- there is no need for a method `Basket::addProduct($productCode, $quantity)`; we are always adding one
  product at a time - I assume that it is specific for Acme's products / industry
- price values are in cents, not dollars, to avoid issues with floating points / rounding when dealing with money
- conversion from and to cents should be done in UI layer (out of the scope of this library)
- order of delivery rules given in configuration matters (first match wins) and configuration has to be coherent;
  ideally should be validated beforehand
- configuration should be provided by instantiating PHP objects - as described in `Example of configuration...` section;
  serialization / deserialization from lower level formats like PHP array or YAML could be added in the future

## How to test it locally

Make sure you have `PHP 8.1` with `bcmath` extension (should be installed and enabled by default)
and `composer` installed. Then:
```
composer install
composer run test 
# ... or: ./vendor/bin/phpunit ./tests
```

The best example of overall usage of this library is in the `./tests/OverallSystemTest.php` file. It contains
values from original instruction to test if calculated results are correct.

## Roadmap / TODO

1. Add tests for the methods that deal with multiplication and division of any pricing values (`\Acme\Domain\Discount` namespace)
2. Add separate unit tests for DiscountApplicator class
3. Add suggestion / infra for serialization / deserialization of the internal state of the basket (provide convenient DTO object)
4. Add suggestion / infra for serialization / deserialization of the configuration data (products, discounts and delivery cost rules)

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
    // this is example of "Second item by half price" discount
    new QuantityBasedDiscount(
      'DISCOUNT-01', // unique id of a discount algorithm
      'C1', // product code that triggers this discount
      'C1', // product code that will be discounted
       50, // percentage of a discount 0 -> 100
       2, // how many units with given product code have to be added to the basket to activate this discount
       1, // how many units with given product code should be affected by this discount
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
