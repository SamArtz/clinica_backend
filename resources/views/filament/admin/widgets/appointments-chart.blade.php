@php
    use Filament\Support\Facades\FilamentView;

    $color = $this->getColor();
    $heading = $this->getHeading();
    $description = $this->getDescription();
@endphp

<x-filament-widgets::widget class="fi-wi-chart">
    <x-filament::section :description="$description" :heading="$heading">
        <x-slot name="headerEnd">
            <div class="flex items-center gap-2">
                <a href="{{ $this->getPreviousRangeUrl() }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    ← 7 días atrás
                </a>
                <a href="{{ $this->getCurrentRangeUrl() }}" class="inline-flex items-center rounded-lg border border-primary-500 px-3 py-2 text-sm font-medium text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-950/30">
                    Hoy
                </a>
                <a href="{{ $this->getNextRangeUrl() }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    7 días adelante →
                </a>
            </div>
        </x-slot>

        <div>
            <div
                @if (FilamentView::hasSpaMode())
                    x-load="visible"
                @else
                    x-load
                @endif
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                wire:ignore
                x-data="chart({
                    cachedData: @js($this->getCachedData()),
                    options: @js($this->getOptions()),
                    type: @js($this->getType()),
                })"
                @class([
                    match ($color) {
                        'gray' => null,
                        default => 'fi-color-custom',
                    },
                    is_string($color) ? "fi-color-{$color}" : null,
                ])
            >
                <canvas x-ref="canvas" @if ($maxHeight = $this->getMaxHeight()) style="max-height: {{ $maxHeight }}" @endif></canvas>
                <span x-ref="backgroundColorElement" @class([
                    match ($color) {
                        'gray' => 'text-gray-100 dark:text-gray-800',
                        default => 'text-custom-50 dark:text-custom-400/10',
                    },
                ]) @style([
                    \Filament\Support\get_color_css_variables($color, shades: [50, 400], alias: 'widgets::chart-widget.background') => $color !== 'gray',
                ])></span>
                <span x-ref="borderColorElement" @class([
                    match ($color) {
                        'gray' => 'text-gray-400',
                        default => 'text-custom-500 dark:text-custom-400',
                    },
                ]) @style([
                    \Filament\Support\get_color_css_variables($color, shades: [400, 500], alias: 'widgets::chart-widget.border') => $color !== 'gray',
                ])></span>
                <span x-ref="gridColorElement" class="text-gray-200 dark:text-gray-800"></span>
                <span x-ref="textColorElement" class="text-gray-500 dark:text-gray-400"></span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
