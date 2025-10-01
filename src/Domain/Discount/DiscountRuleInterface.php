<?php
declare(strict_types=1);

namespace App\Domain\Discount;

use App\Domain\Product\Product;

interface DiscountRuleInterface
{
  public function percentageFor(Product $product): ?int;
}