<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClinicStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pacientes', (string) Patient::count())
                ->description('Pacientes registrados'),
            Stat::make('Citas de hoy', (string) Appointment::whereDate('appointment_date', today())->count())
                ->description('Agenda del día'),
            Stat::make('Médicos', (string) User::role('doctor')->count())
                ->description('Usuarios con rol doctor'),
            Stat::make('Total de citas', (string) Appointment::count())
                ->description('Histórico registrado'),
        ];
    }
}
