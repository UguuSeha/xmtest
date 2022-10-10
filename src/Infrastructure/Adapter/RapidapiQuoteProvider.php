<?php
declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Domain\Model\Quote;
use App\Domain\Port\QuoteProvider;
use Carbon\Carbon;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RapidapiQuoteProvider implements QuoteProvider
{
    public function __construct(
        private HttpClientInterface $rapidapiClient,
    ) {
    }

    public function getHistoricalData(
        string $companySymbol,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $request = $this->rapidapiClient->request('GET', '/stock/v3/get-historical-data', [
            'query' => ['symbol' => $companySymbol],
        ]);
        $content = $request->toArray();

        $quotes = [];
        foreach ($content['prices'] as $item) {
            $date = Carbon::createFromTimestamp($item['date'])->setTime(0, 0, 0);
            if ($date < $startDate || $date > $endDate) {
                continue;
            }

            $quotes[] = new Quote(
                $date,
                $item['open'],
                $item['close'],
                $item['low'],
                $item['high'],
                $item['volume'],
            );
        }

        return $quotes;
    }
}
