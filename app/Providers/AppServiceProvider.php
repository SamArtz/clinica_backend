<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\PatientPolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone'));
        Carbon::setLocale(config('app.locale'));
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(MedicalRecord::class, MedicalRecordPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
