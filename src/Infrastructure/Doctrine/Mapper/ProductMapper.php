<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Product\Product;
use App\Domain\Product\Category;
use App\Domain\Product\Price;
use App\Infrastructure\Doctrine\Entity\ProductEntity;

final class ProductMapper
{
  public static function toDomain(ProductEntity $productEntity): Product
  {
    return new Product(
      $productEntity->getSku(),
      $productEntity->getName(),
      new Category($productEntity->getCategory()),
      new Price($productEntity->getPrice())
    );
  }
}