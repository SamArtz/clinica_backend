<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;

class AppointmentAvailability
{
    public static function today(): Carbon
    {
        return now(config('app.timezone'))->startOfDay();
    }

    public static function isPastDate(string $date): bool
    {
        return Carbon::parse($date, config('app.timezone'))->startOfDay()->lt(static::today());
    }

    public static function isWithinDoctorSchedule(int $doctorId, string $date, string $time): bool
    {
        $appointmentDate = Carbon::parse($date, config('app.timezone'));

        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $appointmentDate->dayOfWeekIso)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', static::normalizeTime($time))
            ->whereTime('end_time', '>=', static::normalizeTime($time))
            ->exists();
    }

    public static function hasConflict(int $doctorId, string $date, string $time, ?int $ignoreAppointmentId = null): bool
    {
        return Appointment::query()
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereTime('appointment_time', static::normalizeTime($time))
            ->exists();
    }

    public static function normalizeTime(string $time): string
    {
        return Carbon::createFromFormat(strlen($time) === 5 ? 'H:i' : 'H:i:s', $time, config('app.timezone'))->format('H:i:s');
    }
}
