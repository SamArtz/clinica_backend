<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'assistant', 'doctor']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'assistant']) || $appointment->doctor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'assistant']);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'assistant']);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'assistant']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'assistant']);
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin');
    }
}
