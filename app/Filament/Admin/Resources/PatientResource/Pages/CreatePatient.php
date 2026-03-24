<?php

namespace App\Filament\Admin\Resources\PatientResource\Pages;

use App\Filament\Admin\Resources\PatientResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Nuevo paciente';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Guardar');
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
