<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ClinicStatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stats = Cache::remember('dashboard-clinic-stats', now(config('app.timezone'))->addMinutes(2), function (): array {
            return [
                'patients' => Patient::count(),
                'appointments_today' => Appointment::whereDate('appointment_date', now(config('app.timezone'))->toDateString())->count(),
                'doctors' => User::role('doctor')->count(),
                'appointments_total' => Appointment::count(),
            ];
        });

        return [
            Stat::make('Pacientes', (string) $stats['patients'])->description('Pacientes registrados'),
            Stat::make('Citas de hoy', (string) $stats['appointments_today'])->description('Agenda del día'),
            Stat::make('Médicos', (string) $stats['doctors'])->description('Usuarios con rol doctor'),
            Stat::make('Total de citas', (string) $stats['appointments_total'])->description('Histórico registrado'),
        ];
    }
}
