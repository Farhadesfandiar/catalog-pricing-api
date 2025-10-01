<?php
declare(strict_types=1);

namespace App\Domain\Product;

final class Product
{
  public function __construct(
    private string $sku,
    private string $name,
    private Category $category,
    private Price $price
  ) {
    $this->sku = trim($this->sku);
    $this->name = trim($this->name);

    if ($this->sku === '') {
      throw new \InvalidArgumentException('Invalid SKU.');
    }
    if ($this->name === '') {
      throw new \InvalidArgumentException('Name can not be empty.');
    }
  }
  public function sku(): string { return $this->sku; }
  public function name(): string { return $this->name; }
  public function category(): Category { return $this->category; }
  public function price(): Price { return $this->price; }

}





