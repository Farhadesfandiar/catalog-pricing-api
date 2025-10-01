<?php
declare(strict_types=1);

use App\Domain\Discount\CategoryDiscountRule;
use App\Domain\Discount\DiscountService;
use App\Domain\Discount\SkuDiscountRule;
use App\Domain\Product\Category;
use App\Domain\Product\Price;
use App\Domain\Product\Product;
use PHPUnit\Framework\TestCase;

final class DiscountServiceTest extends TestCase
{
  public function testMaxDiscountWins(): void
  {
    $svc = new DiscountService([
      new CategoryDiscountRule(new Category('boots'), 30),
      new SkuDiscountRule(['000003' => 15]),
    ]);

    $p = new Product('000003', 'Ashlington boots', new Category('boots'), new Price(10000));
    $priceInfo = $svc->priceFor($p);

    $this->assertSame(10000, $priceInfo->original->amount());
    $this->assertSame(7000, $priceInfo->final->amount());
    $this->assertSame('30%', $priceInfo->toArray()['discount_percentage']);
  }
}