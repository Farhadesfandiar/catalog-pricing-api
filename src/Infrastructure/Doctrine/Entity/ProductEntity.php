<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Domain\Product\Category;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
#[ORM\UniqueConstraint(name: 'unique_product_sku', columns: ['sku'])]
#[ORM\Index(name: 'idx_product_category', columns: ['category'])]
#[ORM\Index(name: 'idx_product_price', columns: ['price'])]
class ProductEntity
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  private ?int $id = null;

  #[ORM\Column(type: 'string', length: 32, unique: true, nullable: false)]
  private string $sku;

  #[ORM\Column(type: 'string', length: 255, nullable: false)]
  private string $name;

  #[ORM\Column(type: 'string', length: 32, nullable: false)]
  private string $category;

  #[ORM\Column(name: 'price', type: 'integer')]
  private int $price;

  public function __construct(string $sku, string $name, string $category, int $price)
  {
    $this->sku = $sku;
    $this->name = $name;
    $this->category = $category;
    $this->price = $price;
  }

  public function getSku(): string
  {
    return $this->sku;
  }
  public function getName(): string
  {
    return $this->name;
  }
  public function getCategory(): string
  {
    return $this->category;
  }
  public function getPrice(): int
  {
    return $this->price;
  }
}




