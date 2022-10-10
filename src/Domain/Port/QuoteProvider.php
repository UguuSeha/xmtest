<?php
declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Quote;

interface QuoteProvider
{
    /**
     * @return Quote[]
     */
    public function getHistoricalData(
        string $companySymbol,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array;
}
