<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Immutable money value object. Internally always paise (integer, no
 * floating point). All money is stored as integer paise in the database —
 * this class plus App\Casts\MoneyCast are the only place `/100` or `*100`
 * arithmetic should appear in application code.
 */
final class Money
{
    private function __construct(
        private readonly int $paise,
    ) {}

    public static function fromPaise(int $paise): self
    {
        return new self($paise);
    }

    public static function fromRupees(int|float|string $rupees): self
    {
        return new self((int) round(((float) $rupees) * 100));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function paise(): int
    {
        return $this->paise;
    }

    public function rupees(): float
    {
        return $this->paise / 100;
    }

    public function add(Money $other): self
    {
        return new self($this->paise + $other->paise);
    }

    public function subtract(Money $other): self
    {
        return new self($this->paise - $other->paise);
    }

    public function multiply(int|float $factor): self
    {
        return new self((int) round($this->paise * $factor));
    }

    public function isZero(): bool
    {
        return $this->paise === 0;
    }

    public function isNegative(): bool
    {
        return $this->paise < 0;
    }

    public function greaterThan(Money $other): bool
    {
        return $this->paise > $other->paise;
    }

    public function lessThan(Money $other): bool
    {
        return $this->paise < $other->paise;
    }

    public function equals(Money $other): bool
    {
        return $this->paise === $other->paise;
    }

    public static function min(Money ...$amounts): self
    {
        if ($amounts === []) {
            throw new InvalidArgumentException('Money::min() requires at least one amount.');
        }

        return array_reduce(
            $amounts,
            fn (?self $carry, self $amount) => $carry === null || $amount->lessThan($carry) ? $amount : $carry,
        );
    }

    public function format(): string
    {
        return '₹'.number_format($this->rupees(), 2);
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
