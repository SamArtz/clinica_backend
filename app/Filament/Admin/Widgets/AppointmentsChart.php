<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class AppointmentsChart extends ChartWidget
{
    protected static ?string $heading = 'Citas por día (7 días)';

    protected function getData(): array
    {
        $period = CarbonPeriod::create(now()->subDays(6)->startOfDay(), now()->startOfDay());

        $labels = [];
        $data = [];

        foreach ($period as $date) {
            $labels[] = $date->format('d/m');
            $data[] = Appointment::whereDate('appointment_date', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Citas',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
