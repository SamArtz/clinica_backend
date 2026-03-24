<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PatientResource\Pages;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Operación Clínica';

    protected static ?string $modelLabel = 'Paciente';

    protected static ?string $pluralModelLabel = 'Pacientes';

    public static function form(Form $form): Form
    {
        $canEditPatientData = auth()->user()?->hasAnyRole(['admin', 'assistant']) ?? false;
        $canEditMedicalRecord = auth()->user()?->hasAnyRole(['admin', 'doctor']) ?? false;

        return $form->schema([
            Section::make('Datos del paciente')
                ->columns(2)
                ->schema([
                    TextInput::make('first_name')->label('Nombres')->required()->maxLength(255)->disabled(! $canEditPatientData),
                    TextInput::make('last_name')->label('Apellidos')->required()->maxLength(255)->disabled(! $canEditPatientData),
                    TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true)->disabled(! $canEditPatientData),
                    TextInput::make('phone')->label('Teléfono')->tel()->maxLength(50)->unique(ignoreRecord: true)->disabled(! $canEditPatientData),
                    DatePicker::make('birth_date')->label('Fecha de nacimiento')->native(false)->required()->disabled(! $canEditPatientData),
                    TextInput::make('document_number')->label('Documento')->unique(ignoreRecord: true)->disabled(! $canEditPatientData),
                    Textarea::make('address')->label('Dirección')->columnSpanFull()->disabled(! $canEditPatientData),
                ]),
            Section::make('Expediente clínico')
                ->relationship('medicalRecord')
                ->columns(2)
                ->schema([
                    TextInput::make('blood_type')->label('Tipo de sangre')->maxLength(10)->disabled(! $canEditMedicalRecord),
                    TextInput::make('current_medications')->label('Medicamentos actuales')->disabled(! $canEditMedicalRecord),
                    Textarea::make('allergies')->label('Alergias')->disabled(! $canEditMedicalRecord),
                    Textarea::make('chronic_diseases')->label('Enfermedades crónicas')->disabled(! $canEditMedicalRecord),
                    Textarea::make('family_history')->label('Antecedentes familiares')->columnSpanFull()->disabled(! $canEditMedicalRecord),
                    TextInput::make('height')->label('Estatura (m)')->numeric()->minValue(1)->rule('gte:1')->disabled(! $canEditMedicalRecord),
                    TextInput::make('weight')->label('Peso (kg)')->numeric()->minValue(1)->rule('gte:1')->disabled(! $canEditMedicalRecord),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Paciente')
                ->columns(2)
                ->schema([
                    TextEntry::make('first_name')->label('Nombres'),
                    TextEntry::make('last_name')->label('Apellidos'),
                    TextEntry::make('email')->label('Correo'),
                    TextEntry::make('phone')->label('Teléfono'),
                    TextEntry::make('birth_date')->date()->label('Fecha de nacimiento'),
                    TextEntry::make('document_number')->label('Documento'),
                    TextEntry::make('address')->label('Dirección')->columnSpanFull(),
                ]),
            InfoSection::make('Expediente clínico')
                ->columns(2)
                ->schema([
                    TextEntry::make('medicalRecord.blood_type')->label('Tipo de sangre'),
                    TextEntry::make('medicalRecord.current_medications')->label('Medicamentos actuales'),
                    TextEntry::make('medicalRecord.allergies')->label('Alergias'),
                    TextEntry::make('medicalRecord.chronic_diseases')->label('Enfermedades crónicas'),
                    TextEntry::make('medicalRecord.family_history')->label('Antecedentes familiares')->columnSpanFull(),
                    TextEntry::make('medicalRecord.height')->label('Estatura (m)'),
                    TextEntry::make('medicalRecord.weight')->label('Peso (kg)'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label('Paciente')->searchable(['first_name', 'last_name'])->sortable(),
                TextColumn::make('email')->label('Correo')->searchable(),
                TextColumn::make('phone')->label('Teléfono')->searchable(),
                TextColumn::make('document_number')->label('Documento')->searchable(),
                TextColumn::make('birth_date')->label('Nacimiento')->date(),
                TextColumn::make('created_at')->label('Registro')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Eliminar')->visible(fn () => auth()->user()?->hasRole('admin')),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'birth_date', 'document_number', 'created_at'])
            ->with('medicalRecord');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'assistant']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'view' => Pages\ViewPatient::route('/{record}'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
