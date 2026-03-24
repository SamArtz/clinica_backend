<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $roleName = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleName = $this->data['role_name'] ?? null;
        unset($data['role_name']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->roleName) {
            $this->record->syncRoles([$this->roleName]);
        }
    }

    public function getTitle(): string
    {
        return 'Nuevo usuario';
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
