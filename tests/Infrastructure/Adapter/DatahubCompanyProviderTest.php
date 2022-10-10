<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure\Adapter;

use App\Infrastructure\Adapter\DatahubCompanyProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DatahubCompanyProviderTest extends TestCase
{
    private const STUB_RESPONSE = '[
    {"Company Name": "iShares MSCI All Country Asia Information Technology Index Fund", "Financial Status": "N", "Market Category": "G", "Round Lot Size": 100.0, "Security Name": "iShares MSCI All Country Asia Information Technology Index Fund", "Symbol": "AAIT", "Test Issue": "N"},
    {"Company Name": "American Airlines Group, Inc.", "Financial Status": "N", "Market Category": "Q", "Round Lot Size": 100.0, "Security Name": "American Airlines Group, Inc. - Common Stock", "Symbol": "AAL", "Test Issue": "N"},
    {"Company Name": "Atlantic American Corporation", "Financial Status": "N", "Market Category": "G", "Round Lot Size": 100.0, "Security Name": "Atlantic American Corporation - Common Stock", "Symbol": "AAME", "Test Issue": "N"},
    {"Company Name": "Applied Optoelectronics, Inc.", "Financial Status": "N", "Market Category": "G", "Round Lot Size": 100.0, "Security Name": "Applied Optoelectronics, Inc. - Common Stock", "Symbol": "AAOI", "Test Issue": "N"},
    {"Company Name": "AAON, Inc.", "Financial Status": "N", "Market Category": "Q", "Round Lot Size": 100.0, "Security Name": "AAON, Inc. - Common Stock", "Symbol": "AAON", "Test Issue": "N"}
    ]';

    public function testGetAll()
    {
        $expectedUrl = 'https://test.com/get';
        $mockResponse = new MockResponse(self::STUB_RESPONSE, [
            'response_headers' => ['Content-Type: application/json'],
        ]);
        $httpClient = new MockHttpClient($mockResponse);

        $provider = new DatahubCompanyProvider($httpClient, $expectedUrl);

        $companies = $provider->getAll();

        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertSame($expectedUrl, $mockResponse->getRequestUrl());

        self::assertCount(5, $companies);
        self::assertSame('iShares MSCI All Country Asia Information Technology Index Fund', $companies[0]->getName());
        self::assertSame('AAIT', $companies[0]->getSymbol());
    }

    public function testFindBySymbol()
    {
        $expectedUrl = 'https://test.com/get';
        $mockResponse = new MockResponse(self::STUB_RESPONSE, [
            'response_headers' => ['Content-Type: application/json'],
        ]);
        $httpClient = new MockHttpClient($mockResponse);

        $provider = new DatahubCompanyProvider($httpClient, $expectedUrl);

        $company = $provider->findBySymbol('AAME');

        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertSame($expectedUrl, $mockResponse->getRequestUrl());

        self::assertNotNull($company);
        self::assertSame('Atlantic American Corporation', $company->getName());
        self::assertSame('AAME', $company->getSymbol());
    }

    public function testFindBySymbolNotFound()
    {
        $expectedUrl = 'https://test.com/get';
        $mockResponse = new MockResponse(self::STUB_RESPONSE, [
            'response_headers' => ['Content-Type: application/json'],
        ]);
        $httpClient = new MockHttpClient($mockResponse);

        $provider = new DatahubCompanyProvider($httpClient, $expectedUrl);

        $company = $provider->findBySymbol('AAMX');

        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertSame($expectedUrl, $mockResponse->getRequestUrl());

        self::assertNull($company);
    }
}
