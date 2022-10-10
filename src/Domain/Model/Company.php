<?php
declare(strict_types=1);

namespace App\Domain\Model;

class Company
{
    public function __construct(
        private string $symbol,
        private string $name
    ) {
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
