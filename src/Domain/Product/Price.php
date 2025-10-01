<?php
declare(strict_types=1);

namespace App\Domain\Product;

final class Price
{
  public const CURRENCY_EUR = 'EUR';

  private int $amount; // cents
  private string $currency;

  public function __construct(int $amount, string $currency = self::CURRENCY_EUR ) {
    if ($amount < 0) {
      throw new \InvalidArgumentException('Price must be >= 0.');
    }

    if ($currency !== self::CURRENCY_EUR) {
      throw new \InvalidArgumentException('Only EUR currency is supported');
    }

    $this->amount = $amount;
    $this->currency = $currency;
  }

  public function amount(): int
  {
    return $this->amount;
  }

  public function currency(): string
  {
    return $this->currency;
  }

  public function withDiscount(int $percent): self
  {
    if ($percent < 1 || $percent > 100) {
      throw new \InvalidArgumentException('Wrong value for discount percentage.');
    }

    // todo: check the accuracy of the calculation
    $final = intdiv($this->amount * (100 - $percent), 100);

    return new self($final, $this->currency);
  }
}





