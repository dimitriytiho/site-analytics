<x-filament-panels::page>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('site-analytics::report.period') }}">
            @foreach (\Omegakontur\SiteAnalytics\Report::PERIODS as $range)
                <x-filament::button
                    :color="$period === $range ? 'primary' : 'gray'"
                    :aria-pressed="$period === $range ? 'true' : 'false'"
                    wire:click="selectPeriod('{{ $range }}')"
                >{{ __('site-analytics::report.'.$range) }}</x-filament::button>
            @endforeach
        </div>
        <div class="grid gap-1">
            <label for="site-analytics-metric" class="text-sm font-medium">{{ __('site-analytics::report.metric') }}</label>
            <x-filament::input.wrapper>
                <x-filament::input.select id="site-analytics-metric" wire:model.live="metric">
                    @foreach ($events as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($highlights as $key => $label)
            <x-filament::section compact>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ $label }}</div>
                <div class="text-2xl font-semibold tabular-nums">{{ number_format($counts[$key] ?? 0, 0, ',', ' ') }}</div>
            </x-filament::section>
        @endforeach
    </div>

    <livewire:site-analytics-chart :period="$period" :metric="$metric" />

    <x-filament::section :heading="__('site-analytics::report.top')">
        @if ($pages === [])
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('site-analytics::report.empty') }}</p>
        @else
            <table class="w-full text-sm">
                <thead class="text-gray-600 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="pb-2 text-start">{{ __('site-analytics::report.page') }}</th>
                        <th scope="col" class="pb-2 text-end">{{ __('site-analytics::report.views') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $code => $total)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="py-2 text-start">{{ $labels[$code]['label'] ?? $code }}</td>
                            <td class="py-2 text-end tabular-nums">{{ number_format($total, 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
