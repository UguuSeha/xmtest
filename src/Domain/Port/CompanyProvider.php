<?php
declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Company;

interface CompanyProvider
{
    /**
     * @return Company[]
     */
    public function getAll(): array;

    public function findBySymbol(string $symbol): ?Company;
}
