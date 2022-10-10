<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure\Adapter;

use App\Domain\Model\Quote;
use App\Infrastructure\Adapter\RapidapiQuoteProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RapidapiQuoteProviderTest extends TestCase
{
    private const STUB_RESPONSE = '{"prices":[
    {"date":1665149400,"open":1.2699999809265137,"high":1.309999942779541,"low":1.2300000190734863,"close":1.2400000095367432,"volume":2419400,"adjclose":1.2400000095367432},
    {"date":1665063000,"open":1.190000057220459,"high":1.2999999523162842,"low":1.1799999475479126,"close":1.2799999713897705,"volume":3963100,"adjclose":1.2799999713897705},
    {"date":1664976600,"open":1.2000000476837158,"high":1.2100000381469727,"low":1.1399999856948853,"close":1.190000057220459,"volume":1009000,"adjclose":1.190000057220459}
    ]}';

    public function testGeHistoricalData()
    {
        $mockResponse = new MockResponse(self::STUB_RESPONSE, [
            'response_headers' => ['Content-Type: application/json'],
        ]);
        $httpClient = new MockHttpClient($mockResponse, 'https://test.com/');

        $provider = new RapidapiQuoteProvider($httpClient);

        $quotes = $provider->getHistoricalData(
            'TEST',
            new \DateTime('2022-10-01 midnight'),
            new \DateTime('2022-10-07 midnight'),
        );

        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertSame('https://test.com/stock/v3/get-historical-data?symbol=TEST', $mockResponse->getRequestUrl());

        self::assertCount(3, $quotes);
        self::assertEquals(new Quote(
            date: new \DateTime('2022-10-07 midnight'),
            open: 1.2699999809265137,
            close: 1.2400000095367432,
            low: 1.2300000190734863,
            high: 1.309999942779541,
            volume: 2419400,
        ), $quotes[0]);
    }

    public function testGeHistoricalDataFilter()
    {
        $mockResponse = new MockResponse(self::STUB_RESPONSE, [
            'response_headers' => ['Content-Type: application/json'],
        ]);
        $httpClient = new MockHttpClient($mockResponse, 'https://test.com/');

        $provider = new RapidapiQuoteProvider($httpClient);

        $quotes = $provider->getHistoricalData(
            'TEST',
            new \DateTime('2022-10-05 midnight'),
            new \DateTime('2022-10-06 midnight'),
        );

        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertSame('https://test.com/stock/v3/get-historical-data?symbol=TEST', $mockResponse->getRequestUrl());

        self::assertCount(2, $quotes);
        self::assertEquals(new \DateTime('2022-10-06 midnight'), $quotes[0]->getDate());
        self::assertEquals(new \DateTime('2022-10-05 midnight'), $quotes[1]->getDate());
    }
}
