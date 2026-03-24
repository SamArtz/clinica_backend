<?php

namespace App\Filament\Admin\Resources\AppointmentResource\Pages;

use App\Filament\Admin\Resources\AppointmentResource;
use App\Support\AppointmentAvailability;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\QueryException;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->guardAvailability($data);

        $data['appointment_time'] = AppointmentAvailability::normalizeTime($data['appointment_time']);

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23505') {
                Notification::make()->danger()->title('Esa hora no está disponible.')->send();
                throw new Halt;
            }

            throw $exception;
        }
    }

    protected function guardAvailability(array $data): void
    {
        if (AppointmentAvailability::isPastDate($data['appointment_date'])) {
            Notification::make()->danger()->title('La fecha de la cita no puede estar en el pasado.')->send();
            throw new Halt;
        }

        if (! AppointmentAvailability::isWithinDoctorSchedule((int) $data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            Notification::make()->danger()->title('El médico no tiene horario disponible en ese bloque de tiempo.')->send();
            throw new Halt;
        }

        if (AppointmentAvailability::hasConflict((int) $data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            Notification::make()->danger()->title('Esa hora no está disponible.')->send();
            throw new Halt;
        }
    }

    public function getTitle(): string
    {
        return 'Nueva cita';
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
