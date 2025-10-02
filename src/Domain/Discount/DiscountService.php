<?php
declare(strict_types=1);

namespace App\Domain\Discount;

use App\Domain\Product\Product;

final class DiscountService implements DiscountServiceInterface
{

  /**
   * @param list<DiscountRuleInterface> $rules
   */
  public function __construct(private array $rules)
  {
  }
  public function priceFor(Product $product): PriceInfo
  {
    // When multiple discounts collide, the bigger discount must be applied
    $max = 0;
    
    foreach ($this->rules as $rule) {
      $dp = $rule->percentageFor($product);
      if ($dp !== null && $dp > $max) {
        $max = $dp;
      }
    }
    
    return $max === 0
      ? new PriceInfo($product->price(), $product->price(), null)
      : new PriceInfo($product->price(), $product->price()->withDiscount($max), $max);
  }
}