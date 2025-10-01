<?php
declare(strict_types=1);

namespace App\Infrastructure\InMemory;

use App\Domain\Product\Category;
use App\Domain\Product\Price;
use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;

final class InMemoryProductRepository implements ProductRepositoryInterface
{
  /** @var Product[] */
  private array $products;
  /**
   * @param array<int, array{sku:string,name:string,category:string,price:int}> $seed
   */
  public function __construct(array $seed)
  {
    $this->products = [];
    foreach ($seed as $p) {
      $this->products[] = new Product(
        $p['sku'],
        $p['name'],
        new Category($p['category']),
        new Price($p['price'])
      );
    }
  }

  public function findByFilters(?Category $category, ?int $priceLessThan, int $limit): iterable
  {
    $count = 0;
    foreach ($this->products as $p) {
      if ($category && !$p->category()->equals($category)) {
        continue;
      }

      if ($priceLessThan !== null && $p->price()->amount() > $priceLessThan) {
        continue;
      }

      yield $p;
      if (++$count >= $limit) {
        break;
      }
    }
  }
}




