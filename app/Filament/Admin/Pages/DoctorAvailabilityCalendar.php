<?php

namespace App\Filament\Admin\Pages;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;

class DoctorAvailabilityCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendario médico';

    protected static ?string $navigationGroup = 'Operación Clínica';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Calendario mensual de disponibilidad';

    protected static string $view = 'filament.admin.pages.doctor-availability-calendar';

    public array $days = [];

    public string $monthLabel = '';

    public string $previousMonthUrl = '';

    public string $currentMonthUrl = '';

    public string $nextMonthUrl = '';

    public function mount(): void
    {
        Carbon::setLocale(app()->getLocale());

        $month = max(1, min(12, (int) request()->integer('month', now()->month)));
        $year = (int) request()->integer('year', now()->year);

        $this->buildCalendar(Carbon::createFromDate($year, $month, 1)->startOfMonth());
    }

    protected function buildCalendar(Carbon $currentMonth): void
    {
        $monthStart = $currentMonth->copy()->startOfMonth();
        $monthEnd = $currentMonth->copy()->endOfMonth();

        $doctors = User::role('doctor')
            ->with(['doctorSchedules' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $appointments = Appointment::query()
            ->selectRaw('doctor_id, appointment_date::date as appointment_day, COUNT(*) as total')
            ->whereBetween('appointment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('doctor_id', 'appointment_day')
            ->get()
            ->keyBy(fn ($row) => $row->doctor_id . '|' . Carbon::parse($row->appointment_day)->format('Y-m-d'));

        $days = [];
        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $day = $cursor->copy();
            $doctorEntries = [];

            foreach ($doctors as $doctor) {
                $schedule = $doctor->doctorSchedules->firstWhere('day_of_week', $day->dayOfWeekIso);
                $appointmentCount = $appointments->get($doctor->id . '|' . $day->format('Y-m-d'));

                $doctorEntries[] = [
                    'name' => $doctor->name,
                    'schedule' => $schedule
                        ? substr((string) $schedule->start_time, 0, 5) . ' - ' . substr((string) $schedule->end_time, 0, 5)
                        : null,
                    'appointments' => (int) ($appointmentCount->total ?? 0),
                ];
            }

            $days[] = [
                'date' => $day->format('Y-m-d'),
                'day' => $day->day,
                'weekday' => ucfirst($day->translatedFormat('D')),
                'full_label' => ucfirst($day->translatedFormat('l, d \\d\\e F \\d\\e Y')),
                'is_today' => $day->isToday(),
                'doctors' => $doctorEntries,
            ];

            $cursor->addDay();
        }

        $previousMonth = $currentMonth->copy()->subMonthNoOverflow();
        $nextMonth = $currentMonth->copy()->addMonthNoOverflow();

        $this->days = $days;
        $this->monthLabel = ucfirst($currentMonth->translatedFormat('F Y'));
        $this->previousMonthUrl = static::getUrl(['month' => $previousMonth->month, 'year' => $previousMonth->year]);
        $this->currentMonthUrl = static::getUrl(['month' => now()->month, 'year' => now()->year]);
        $this->nextMonthUrl = static::getUrl(['month' => $nextMonth->month, 'year' => $nextMonth->year]);
    }
}
