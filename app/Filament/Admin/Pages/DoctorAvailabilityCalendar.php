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
        Carbon::setLocale(config('app.locale'));
        date_default_timezone_set(config('app.timezone'));

        $today = now(config('app.timezone'));
        $month = max(1, min(12, (int) request()->integer('month', $today->month)));
        $year = (int) request()->integer('year', $today->year);

        $this->buildCalendar(Carbon::create($year, $month, 1, 0, 0, 0, config('app.timezone'))->startOfMonth());
    }

    protected function buildCalendar(Carbon $currentMonth): void
    {
        $monthStart = $currentMonth->copy()->startOfMonth();
        $monthEnd = $currentMonth->copy()->endOfMonth();

        $doctors = User::role('doctor')
            ->select(['id', 'name'])
            ->with(['doctorSchedules' => fn ($query) => $query
                ->select(['id', 'doctor_id', 'day_of_week', 'start_time', 'end_time', 'is_active'])
                ->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $appointmentCounts = Appointment::query()
            ->selectRaw('doctor_id, appointment_date, COUNT(*) as total')
            ->whereBetween('appointment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('doctor_id', 'appointment_date')
            ->get()
            ->keyBy(fn ($row) => $row->doctor_id . '|' . Carbon::parse($row->appointment_date, config('app.timezone'))->toDateString());

        $days = [];
        $cursor = $monthStart->copy();
        $today = now(config('app.timezone'))->toDateString();

        while ($cursor->lte($monthEnd)) {
            $day = $cursor->copy();
            $doctorEntries = [];
            $dayKey = $day->toDateString();

            foreach ($doctors as $doctor) {
                $schedule = $doctor->doctorSchedules->firstWhere('day_of_week', $day->dayOfWeekIso);
                $appointmentCount = $appointmentCounts->get($doctor->id . '|' . $dayKey);

                $doctorEntries[] = [
                    'name' => $doctor->name,
                    'schedule' => $schedule
                        ? substr((string) $schedule->start_time, 0, 5) . ' - ' . substr((string) $schedule->end_time, 0, 5)
                        : null,
                    'appointments' => (int) ($appointmentCount->total ?? 0),
                ];
            }

            $days[] = [
                'date' => $dayKey,
                'day' => $day->day,
                'weekday' => ucfirst($day->translatedFormat('D')),
                'full_label' => ucfirst($day->translatedFormat('l, d \d\e F \d\e Y')),
                'is_today' => $dayKey === $today,
                'doctors' => $doctorEntries,
            ];

            $cursor->addDay();
        }

        $previousMonth = $currentMonth->copy()->subMonthNoOverflow();
        $nextMonth = $currentMonth->copy()->addMonthNoOverflow();
        $todayDate = now(config('app.timezone'));

        $this->days = $days;
        $this->monthLabel = ucfirst($currentMonth->translatedFormat('F Y'));
        $this->previousMonthUrl = static::getUrl(['month' => $previousMonth->month, 'year' => $previousMonth->year]);
        $this->currentMonthUrl = static::getUrl(['month' => $todayDate->month, 'year' => $todayDate->year]);
        $this->nextMonthUrl = static::getUrl(['month' => $nextMonth->month, 'year' => $nextMonth->year]);
    }
}
