<?php
declare(strict_types=1);

namespace App\Domain\Product;

final class Category
{
  private string $value;
  public function __construct(string $value)
  {
    $normalizedValue = mb_strtolower(trim( $value));

    if($normalizedValue === '') {
      throw new \InvalidArgumentException('Category value cannot be empty.');
    }

    if (!preg_match('/^[a-z0-9_-]+$/', $normalizedValue)) {
      throw new \InvalidArgumentException(
        'Category may contain only letters and numbers.'
      );
    }

    $this->value = $normalizedValue;

  }

  public function value(): string
  {
    return $this->value;
  }

  public function equals(self $other): bool
  {
    return $this->value === $other->value;
  }

  public function __toString(): string
  {
    return $this->value;
  }
}





