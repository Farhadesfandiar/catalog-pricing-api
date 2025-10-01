<?php
declare(strict_types=1);

namespace App\Application;

use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Discount\DiscountServiceInterface;
use App\Domain\Product\Product;
use App\Domain\Discount\PriceInfo;

final class GetProductsHandler
{
  public function __construct(private readonly ProductRepositoryInterface $repo, private readonly DiscountServiceInterface $discounts) {}

  public function __invoke(GetProductsQuery $query): array
  {

    $out = [];
    foreach ($this->repo->findByFilters($query->category, $query->priceLessThan, $query->limit) as $product) {
      $priceInfo = $this->discounts->priceFor($product);
      $out[] = [
        'sku' => $product->sku(),
        'name' => $product->name(),
        'category' => $product->category()->value(),
        'price' => $priceInfo->toArray(),
      ];
    }

    return $out;
  }
}