<?php

namespace Omegakontur\SiteAnalytics;

use Filament\Widgets\ChartWidget;
use Livewire\Attributes\Reactive;

class AnalyticsChart extends ChartWidget
{
    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '260px';

    #[Reactive]
    public string $period = Report::DEFAULT_PERIOD;

    #[Reactive]
    public string $metric = Counter::VIEWS;

    public function getHeading(): string
    {
        return __('site-analytics::report.'.($this->period === '24h' ? 'by_hour' : 'by_day'));
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        abort_unless(AnalyticsPage::canAccess(), 403);
        $events = (array) config('site-analytics.events');
        $metric = isset($events[$this->metric]) ? $this->metric : Counter::VIEWS;
        $series = (new Report($this->period))->series($metric);

        return [
            'datasets' => [['label' => $events[$metric] ?? $metric, 'data' => $series['data']]],
            'labels' => $series['labels'],
        ];
    }
}
