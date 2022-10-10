<?php
declare(strict_types=1);

namespace App\Domain\Model;

class Quote
{
    public function __construct(
        private \DateTimeInterface $date,
        private float $open,
        private float $close,
        private float $low,
        private float $high,
        private int $volume
    ) {
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getOpen(): float
    {
        return $this->open;
    }

    public function getClose(): float
    {
        return $this->close;
    }

    public function getLow(): float
    {
        return $this->low;
    }

    public function getHigh(): float
    {
        return $this->high;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }
}
