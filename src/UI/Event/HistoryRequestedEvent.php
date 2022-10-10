<?php
declare(strict_types=1);

namespace App\UI\Event;

use App\Domain\Model\Company;

class HistoryRequestedEvent
{
    public function __construct(
        public readonly Company $company,
        public readonly \DateTimeInterface $start,
        public readonly \DateTimeInterface $end,
        public readonly string $email,
    ) {
    }
}
