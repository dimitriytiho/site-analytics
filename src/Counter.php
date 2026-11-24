<?php

namespace Omegakontur\SiteAnalytics;

use Illuminate\Support\Facades\DB;
use Throwable;

class Counter
{
    public const string VIEWS = 'page_view';

    public function increment(string $page, string $event): void
    {
        DB::table('site_analytics_counts')->upsert([
            'hour' => now('UTC')->startOfHour()->toDateTimeString(),
            'page' => $page,
            'event' => $event,
            'hits' => 1,
        ], ['hour', 'page', 'event'], ['hits' => DB::raw('site_analytics_counts.hits + 1')]);
    }

    /** Сбой отчёта не должен менять результат операции пользователя. */
    public function record(string $page, string $event): void
    {
        try {
            $this->increment($page, $event);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
