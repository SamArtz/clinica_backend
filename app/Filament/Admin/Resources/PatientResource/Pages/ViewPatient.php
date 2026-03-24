<?php

namespace App\Filament\Admin\Resources\PatientResource\Pages;

use App\Filament\Admin\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    public function getTitle(): string
    {
        return 'Detalle del paciente';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Editar'),
        ];
    }
}
