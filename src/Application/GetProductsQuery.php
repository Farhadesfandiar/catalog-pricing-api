<?php
declare(strict_types=1);

namespace App\Application;

use App\Domain\Product\Category;
/**
 * A small DTO for the GetProductsHandler
 */
final class GetProductsQuery
{
  public function __construct(
    public readonly ?Category $category,
    public readonly ?int $priceLessThan,
    public readonly int $limit = 5
    ) {}
}