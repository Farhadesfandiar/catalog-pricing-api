<?php
declare(strict_types=1);

namespace App\Domain\Discount;

use App\Domain\Product\Product;

/**
 * applies a fixed % when the product’s sku matches one of a sku discount percentage.
 */
final class SkuDiscountRule implements DiscountRuleInterface
{

  /**
   * @param array<string,int> $skuToPercent
   */
  public function __construct(private array $skuToPercent)
  {
  }

  /**
   * @param Product $product
   * @return int|null
   */
  public function percentageFor(Product $product): ?int
  {
    return $this->skuToPercent[$product->sku()] ?? null;
  }
}