<?php

namespace Omegakontur\SiteAnalytics;

use Filament\Contracts\Plugin;
use Filament\Panel;

class AnalyticsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'site-analytics';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([AnalyticsPage::class]);
    }

    public function boot(Panel $panel): void
    {
    }
}
