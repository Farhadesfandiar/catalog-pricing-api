<?php
declare(strict_types=1);

namespace App\Domain\Product;

interface ProductRepositoryInterface
{
  /**
   * @return Product[]
   */
  public function findByFilters(?Category $category, ?int $priceLessThanCents, int $limit): iterable;
}




