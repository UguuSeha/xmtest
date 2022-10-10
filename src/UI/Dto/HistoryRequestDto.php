<?php
declare(strict_types=1);

namespace App\UI\Dto;

use App\Domain\Model\Company;

class HistoryRequestDto
{
    public function __construct(
        public ?Company $company = null,
        public ?\DateTimeInterface $start = null,
        public ?\DateTimeInterface $end = null,
        public ?string $email = null,
    ) {
    }
}
