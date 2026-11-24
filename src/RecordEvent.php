<?php

namespace Omegakontur\SiteAnalytics;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecordEvent
{
    public function __invoke(Request $request, Counter $counter): Response
    {
        abort_unless($request->isJson() && $request->header('Sec-Fetch-Site', 'same-origin') === 'same-origin', 403);
        $page = $request->input('page');
        $event = $request->input('event');
        $pages = (array) config('site-analytics.pages');
        $events = (array) config('site-analytics.browser_events');

        abort_unless(is_string($page) && isset($pages[$page]) && is_string($event)
            && ($event === Counter::VIEWS || in_array($page, $events[$event] ?? [], true)), 422);

        $counter->increment($page, $event);

        return response()->noContent();
    }
}
