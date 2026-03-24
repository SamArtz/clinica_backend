<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Support\AppointmentAvailability;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operación Clínica';

    protected static ?string $modelLabel = 'Cita';

    protected static ?string $pluralModelLabel = 'Citas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Detalle de la cita')
                ->columns(2)
                ->schema([
                    Select::make('patient_id')
                        ->label('Paciente')
                        ->searchable()
                        ->preload(false)
                        ->required()
                        ->getSearchResultsUsing(fn (string $search): array => Patient::query()
                            ->select(['id', 'first_name', 'last_name', 'document_number', 'phone'])
                            ->where(function ($query) use ($search): void {
                                $query
                                    ->where('first_name', 'ilike', "%{$search}%")
                                    ->orWhere('last_name', 'ilike', "%{$search}%")
                                    ->orWhere('document_number', 'ilike', "%{$search}%")
                                    ->orWhere('phone', 'ilike', "%{$search}%");
                            })
                            ->orderBy('first_name')
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn (Patient $patient): array => [$patient->id => $patient->full_name . ' · ' . ($patient->document_number ?: 'Sin documento')])
                            ->all())
                        ->getOptionLabelUsing(fn ($value): ?string => Patient::find($value)?->full_name),
                    Select::make('doctor_id')
                        ->label('Médico')
                        ->options(User::role('doctor')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('appointment_date')
                        ->label('Fecha')
                        ->native(false)
                        ->minDate(AppointmentAvailability::today())
                        ->required()
                        ->rule('after_or_equal:' . AppointmentAvailability::today()->toDateString()),
                    TimePicker::make('appointment_time')
                        ->label('Hora')
                        ->seconds(false)
                        ->required(),
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'confirmed' => 'Confirmada',
                            'completed' => 'Completada',
                            'cancelled' => 'Cancelada',
                        ])
                        ->default('pending')
                        ->required(),
                    Textarea::make('reason')->label('Motivo')->columnSpanFull(),
                    Textarea::make('notes')->label('Notas')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_date')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('appointment_time')->label('Hora')->time('H:i')->sortable(),
                TextColumn::make('patient.full_name')->label('Paciente')->searchable(['first_name', 'last_name']),
                TextColumn::make('doctor.name')->label('Médico')->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
                TextColumn::make('reason')->label('Motivo')->limit(40),
            ])
            ->filters([
                SelectFilter::make('doctor_id')
                    ->label('Médico')
                    ->relationship('doctor', 'name')
                    ->visible(fn () => ! (auth()->user()?->hasRole('doctor') ?? false)),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ]),
                Filter::make('today')
                    ->label('Solo hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('appointment_date', AppointmentAvailability::today())),
            ])
            ->defaultSort('appointment_date')
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar')->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'assistant'])),
                Tables\Actions\DeleteAction::make()->label('Eliminar')->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'assistant'])),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->select(['id', 'doctor_id', 'patient_id', 'appointment_date', 'appointment_time', 'reason', 'notes', 'status', 'created_at', 'updated_at'])
            ->with([
                'doctor:id,name',
                'patient:id,first_name,last_name',
            ]);

        $user = auth()->user();

        if ($user?->hasRole('doctor')) {
            $query->where('doctor_id', $user->id);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'assistant']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'assistant']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'assistant']) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'assistant']) ?? false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
