<?php

namespace Omegakontur\SiteAnalytics;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Report
{
    public const string DEFAULT_PERIOD = '7d';

    public const array PERIODS = ['24h', '7d', '30d'];

    private readonly CarbonImmutable $end;

    private readonly CarbonImmutable $start;

    private readonly bool $hourly;

    public function __construct(string $period)
    {
        $this->end = CarbonImmutable::now(config('app.timezone'));
        $this->hourly = $period === '24h';
        $this->start = match ($period) {
            '24h' => $this->end->startOfHour()->subUTCUnit('hour', 23),
            '30d' => $this->end->startOfDay()->subDays(29),
            default => $this->end->startOfDay()->subDays(6),
        };
    }

    private function query(): Builder
    {
        return DB::table('site_analytics_counts')->whereBetween('hour', [
            $this->start->utc()->toDateTimeString(),
            $this->end->utc()->toDateTimeString(),
        ]);
    }

    /** @return array<string, int> */
    public function totals(): array
    {
        return $this->query()->selectRaw('event, SUM(hits) as total')->groupBy('event')
            ->pluck('total', 'event')->map(fn ($total) => (int) $total)->all();
    }

    /** @return array<string, int> */
    public function pages(): array
    {
        return $this->query()->where('event', Counter::VIEWS)
            ->selectRaw('page, SUM(hits) as total')->groupBy('page')->orderByDesc('total')->orderBy('page')
            ->pluck('total', 'page')->map(fn ($total) => (int) $total)->all();
    }

    /** @return array{labels: list<string>, data: list<int>} */
    public function series(string $event): array
    {
        $hits = [];
        foreach ($this->query()->where('event', $event)->selectRaw('hour, SUM(hits) as total')->groupBy('hour')->get() as $row) {
            $moment = CarbonImmutable::parse($row->hour, 'UTC')->setTimezone($this->start->getTimezone());
            $key = $this->key($moment);
            $hits[$key] = ($hits[$key] ?? 0) + (int) $row->total;
        }
        $data = [];
        $labels = [];
        for ($cursor = $this->start; $cursor <= $this->end;
            $cursor = $this->hourly ? $cursor->addUTCUnit('hour') : $cursor->addDay()) {
            $labels[] = $cursor->format($this->hourly ? 'H:i' : 'd.m');
            $data[] = $hits[$this->key($cursor)] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function key(CarbonImmutable $moment): string
    {
        return $this->hourly ? (string) $moment->startOfHour()->timestamp : $moment->toDateString();
    }
}
