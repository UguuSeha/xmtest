<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Domain\Model\Company;
use App\Domain\Port\CompanyProvider;
use Psr\Cache\CacheItemPoolInterface;

class CachingCompanyProvider implements CompanyProvider
{
    private const LIST_KEY = 'CachingCompanyProvider_getAll';
    private const EXPIRE_AFTER = 60 * 10; // 10 min

    public function __construct(
        private CompanyProvider $delegate,
        private CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    public function getAll(): array
    {
        return array_values($this->getAllCompaniesIndexed());
    }

    public function findBySymbol(string $symbol): ?Company
    {
        return $this->getAllCompaniesIndexed()[$symbol] ?? null;
    }

    private function getAllCompaniesIndexed(): array
    {
        $item = $this->cacheItemPool->getItem(self::LIST_KEY);
        if ($item->isHit()) {
            return $item->get();
        }

        $companies = [];
        foreach ($this->delegate->getAll() as $company) {
            $companies[$company->getSymbol()] = $company;
        }

        $item->set($companies);
        $item->expiresAfter(self::EXPIRE_AFTER);
        $this->cacheItemPool->saveDeferred($item);

        return $companies;
    }
}
