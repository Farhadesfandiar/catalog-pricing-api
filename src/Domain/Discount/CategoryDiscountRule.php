<?php
declare(strict_types=1);

namespace App\Domain\Discount;

use App\Domain\Product\Category;
use App\Domain\Product\Product;

final class CategoryDiscountRule implements DiscountRuleInterface
{
  public function __construct(private Category $category, private int $percent)
  {
    if ($percent < 1 || $percent > 100) {
      throw new \InvalidArgumentException('Invalid discount percentage. [1..100]');
    }
  }

  public function percentageFor(Product $product): ?int
  {
    return $product->category()->equals($this->category) ? $this->percent : null;
  }
}