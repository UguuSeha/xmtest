<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Domain\Model\Company;
use App\Domain\Port\CompanyProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DatahubCompanyProvider implements CompanyProvider
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $datahubUrl,
    ) {
    }

    public function getAll(): array
    {
        $response = $this->httpClient->request('GET', $this->datahubUrl);
        $content = $response->toArray();

        $companies = [];
        foreach ($content as $item) {
            $companies[] = new Company($item['Symbol'], $item['Company Name']);
        }

        return $companies;
    }

    public function findBySymbol(string $symbol): ?Company
    {
        foreach ($this->getAll() as $company) {
            if ($company->getSymbol() === $symbol) {
                return $company;
            }
        }

        return null;
    }
}
