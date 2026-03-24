<?php

namespace App\Filament\Admin\Resources\AppointmentResource\Pages;

use App\Filament\Admin\Resources\AppointmentResource;
use App\Support\AppointmentAvailability;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\QueryException;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (AppointmentAvailability::isPastDate($data['appointment_date'])) {
            Notification::make()->danger()->title('La fecha de la cita no puede estar en el pasado.')->send();
            throw new Halt;
        }

        if (! AppointmentAvailability::isWithinDoctorSchedule((int) $data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            Notification::make()->danger()->title('El médico no tiene horario disponible en ese bloque de tiempo.')->send();
            throw new Halt;
        }

        if (AppointmentAvailability::hasConflict((int) $data['doctor_id'], $data['appointment_date'], $data['appointment_time'], $this->getRecord()->id)) {
            Notification::make()->danger()->title('Esa hora no está disponible.')->send();
            throw new Halt;
        }

        $data['appointment_time'] = AppointmentAvailability::normalizeTime($data['appointment_time']);

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23505') {
                Notification::make()->danger()->title('Esa hora no está disponible.')->send();
                throw new Halt;
            }

            throw $exception;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Eliminar')->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'assistant'])),
        ];
    }

    public function getTitle(): string
    {
        return 'Editar cita';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Guardar cambios');
    }
}
