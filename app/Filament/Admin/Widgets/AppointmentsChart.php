<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class AppointmentsChart extends ChartWidget
{
    protected static ?string $heading = 'Citas por día';

    protected static string $color = 'primary';

    protected static ?string $maxHeight = '320px';

    protected static string $view = 'filament.admin.widgets.appointments-chart';

    protected int | string | array $columnSpan = 'full';

    public int $rangeOffset = 0;

    public function mount(): void
    {
        $this->rangeOffset = (int) request()->integer('range', 0);

        parent::mount();
    }

    public function getHeading(): string | Htmlable | null
    {
        return new HtmlString('Citas por día');
    }

    public function getDescription(): string | Htmlable | null
    {
        return 'Del ' . $this->getStartDate()->format('d/m/Y') . ' al ' . $this->getEndDate()->format('d/m/Y');
    }

    public function getPreviousRangeUrl(): string
    {
        return $this->getRangeUrl($this->rangeOffset - 1);
    }

    public function getCurrentRangeUrl(): string
    {
        return $this->getRangeUrl(0);
    }

    public function getNextRangeUrl(): string
    {
        return $this->getRangeUrl($this->rangeOffset + 1);
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $totals = Appointment::query()
            ->selectRaw('appointment_date, COUNT(*) as total')
            ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('appointment_date')
            ->orderBy('appointment_date')
            ->pluck('total', 'appointment_date')
            ->mapWithKeys(fn ($total, $date) => [Carbon::parse($date, config('app.timezone'))->toDateString() => (int) $total])
            ->all();

        $labels = [];
        $values = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $labels[] = $cursor->format('d/m');
            $values[] = $totals[$cursor->toDateString()] ?? 0;
            $cursor->addDay();
        }

        return [
            'datasets' => [[
                'label' => 'Citas',
                'data' => $values,
                'fill' => false,
                'tension' => 0.25,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getStartDate(): Carbon
    {
        return now(config('app.timezone'))->startOfDay()->addDays($this->rangeOffset * 7);
    }

    protected function getEndDate(): Carbon
    {
        return $this->getStartDate()->copy()->addDays(6);
    }

    protected function getRangeUrl(int $offset): string
    {
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'admin';

        return Dashboard::getUrl(['range' => $offset], panel: $panelId);
    }
}
