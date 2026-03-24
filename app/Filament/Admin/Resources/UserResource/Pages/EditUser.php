<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $roleName = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleName = $this->data['role_name'] ?? null;
        unset($data['role_name']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->roleName) {
            $this->record->syncRoles([$this->roleName]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Eliminar')->visible(fn () => auth()->id() !== $this->record->id),
        ];
    }

    public function getTitle(): string
    {
        return 'Editar usuario';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Guardar cambios');
    }
}
