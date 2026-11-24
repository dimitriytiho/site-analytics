<?php

namespace Omegakontur\SiteAnalytics;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class AnalyticsPage extends Page
{
    protected static ?string $slug = 'site-analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'site-analytics::overview';

    public string $period = Report::DEFAULT_PERIOD;

    public string $metric = Counter::VIEWS;

    public static function canAccess(): bool
    {
        return Gate::allows('view-site-analytics');
    }

    public static function getNavigationLabel(): string
    {
        return __('site-analytics::report.title');
    }

    public function getTitle(): string
    {
        return self::getNavigationLabel();
    }

    public function selectPeriod(string $period): void
    {
        if (in_array($period, Report::PERIODS, true)) {
            $this->period = $period;
        }
    }

    protected function getViewData(): array
    {
        abort_unless(static::canAccess(), 403);
        $events = array_map(fn (string $label): string => __($label), (array) config('site-analytics.events'));
        $report = new Report($this->period);

        return [
            'events' => $events,
            'labels' => (array) config('site-analytics.pages'),
            'highlights' => array_intersect_key($events, array_flip((array) config('site-analytics.highlights'))),
            'counts' => $report->totals(),
            'pages' => $report->pages(),
        ];
    }
}
