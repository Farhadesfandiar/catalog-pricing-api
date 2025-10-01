<?php
declare(strict_types=1);

namespace App\Domain\Discount;

use App\Domain\Product\Product;

interface DiscountServiceInterface
{
  public function priceFor(Product $product): PriceInfo;
}