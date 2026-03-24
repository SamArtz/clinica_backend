<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'assistant', 'doctor']);
    }

    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasAnyRole(['admin', 'assistant', 'doctor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole('admin');
    }
}
