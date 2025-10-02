<?php
declare(strict_types=1);

use App\Domain\Discount\CategoryDiscountRule;
use App\Domain\Discount\DiscountService;
use App\Domain\Discount\SkuDiscountRule;
use App\Domain\Product\Category;
use App\Domain\Product\Price;
use App\Domain\Product\Product;
use App\Infrastructure\InMemory\InMemoryProductRepository;
use PHPUnit\Framework\TestCase;

final class DiscountServiceTest extends TestCase
{
  private $seed = [];
  private $discountRules = [];

  public function setUp(): void
  {
    $this->discountRules = [
      new CategoryDiscountRule(new Category('boots'), 30),
      new SkuDiscountRule(['000003' => 15]),
    ];

    $this->seed = [
      ['sku' => '000001', 'name' => 'BV Lean leather ankle boots', 'category' => 'boots', 'price' => 89000],
      ['sku' => '000002', 'name' => 'BV Lean leather ankle boots', 'category' => 'boots', 'price' => 99000],
      ['sku' => '000003', 'name' => 'Ashlington leather ankle boots', 'category' => 'boots', 'price' => 71000],
      ['sku' => '000004', 'name' => 'Naima embellished suede sandals', 'category' => 'sandals', 'price' => 79500],
      ['sku' => '000005', 'name' => 'Nathane leather sneakers', 'category' => 'sneakers', 'price' => 59000],
    ];
  }


  public function testOnlySkuDiscount(): void
  {
    $discounts = new DiscountService($this->discountRules);

    $p = new Product('000003', 'Ashlington some other category', new Category('no-discount-category'), new Price(10000));
    $priceInfo = $discounts->priceFor($p);

    $this->assertSame(10000, $priceInfo->original->amount());
    $this->assertSame(8500, $priceInfo->final->amount());
    self::assertSame('15%', $priceInfo->toArray()['discount_percentage']);
  }

  public function testOnlyCategoryDiscount(): void
  {
    $discounts = new DiscountService($this->discountRules);

    $repo = new InMemoryProductRepository($this->seed);
    $ps = $repo->findByFilters(null, null, 100);
    $p = null;
    foreach ($ps as $p) {
      if ($p->sku() === '000002') {
        $product = $p;
        break;
      }
    }

    self::assertInstanceOf(Product::class, $product, 'Product 000002 not found!');

    $priceInfo = $discounts->priceFor($product);

    $this->assertSame(99000, $priceInfo->original->amount());
    $this->assertSame(69300, $priceInfo->final->amount());
    self::assertSame('30%', $priceInfo->toArray()['discount_percentage']);
  }
  public function testNoDiscountApplicable(): void
  {
    $discounts = new DiscountService($this->discountRules);

    $repo = new InMemoryProductRepository($this->seed);

    $ps = $repo->findByFilters(null, null, 100);
    $product = null;
    foreach ($ps as $p) {
      if ($p->sku() === '000004') {
        $product = $p;
        break;
      }
    }

    self::assertInstanceOf(Product::class, $product, 'Product 000004 not found!');
    $priceInfo = $discounts->priceFor($product);
    self::assertSame(79500, $priceInfo->original->amount());
    self::assertSame(79500, $priceInfo->final->amount());
    self::assertNull($priceInfo->toArray()['discount_percentage']);
  }
  public function testMaxDiscountWins(): void
  {
    $discounts = new DiscountService($this->discountRules);

    $p = new Product('000003', 'Ashlington boots', new Category('boots'), new Price(10000));
    $priceInfo = $discounts->priceFor($p);

    $this->assertSame(10000, $priceInfo->original->amount());
    $this->assertSame(7000, $priceInfo->final->amount());
    $this->assertSame('30%', $priceInfo->toArray()['discount_percentage']);
  }

  public function testPriceRoundingFlooring(): void
  {
    $discounts = new DiscountService([
      new SkuDiscountRule(['000006' => 15]),
      new CategoryDiscountRule(new Category('sandals'), 2)
    ]);

    $p = new Product('000006', 'Sample', new Category('sandals'), new Price(999));
    $priceInfo = $discounts->priceFor($p);
    
    // 999 * 85% = 849.15 → floor to 849
    $this->assertSame(849, $priceInfo->final->amount());
  }

  public function testNotAcceptingNegativePrices(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    new Price(-10000);
  }

}