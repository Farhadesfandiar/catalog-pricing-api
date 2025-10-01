<?php
declare(strict_types=1);

namespace App\Infrastructure\Discount;

use App\Domain\Discount\CategoryDiscountRule;
use App\Domain\Discount\DiscountRuleInterface;
use App\Domain\Discount\DiscountService;
use App\Domain\Discount\SkuDiscountRule;
use App\Domain\Product\Category;

final class DiscountRulesFactory
{
  /** @param array<string,int> $categoryPercents  @param array<string,int> $skuPercents */
  public function __construct(
    private readonly array $categoryPercents,
    private readonly array $skuPercents
  ) {
  }

  /** Build the DiscountService with all active rules from parameters. */
  public function createService(): DiscountService
  {
    $rules = [];

    // Category rules
    foreach ($this->categoryPercents as $slug => $percent) {
      $rules[] = new CategoryDiscountRule(new Category((string) $slug), (int) $percent);
    }

    // Single map rule for SKUs
    if ($this->skuPercents) {
      $map = [];
      foreach ($this->skuPercents as $sku => $percent) {
        $map[(string) $sku] = (int) $percent;
      }
      $rules[] = new SkuDiscountRule($map);
    }

    /** @var DiscountRuleInterface[] $rules */
    return new DiscountService($rules);
  }
}