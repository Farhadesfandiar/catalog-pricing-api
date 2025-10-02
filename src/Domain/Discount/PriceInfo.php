<?php
declare(strict_types=1);

namespace App\Domain\Discount;

use App\Domain\Product\Price;

final class PriceInfo
{
  public function __construct(public Price $original, public Price $final, public ?int $discountPercentage) {}

  /**
   * 
   * @return array{currency: string, discount_percentage: string|null, final: int, original: int}
   */
  public function toArray(): array
  {
    return [
      'original'                => $this->original->amount(),
      'final'                   => $this->final->amount(),
      'discount_percentage'     => $this->discountPercentage ? "{$this->discountPercentage}%" : null,
      'currency'              => $this->original->currency(),
    ];
  }
}