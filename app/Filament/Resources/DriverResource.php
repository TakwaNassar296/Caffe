<?php

namespace App\Filament\Resources;

use Filament\Notifications\Notification;
use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    public static function getNavigationLabel(): string
    {
        return __('strings.drivers');
    }

    public static function getModelLabel(): string
    {
        return __('strings.drivers');
    }

    public static function getPluralModelLabel(): string
    {
        return __('strings.drivers');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_employees_users');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(__('admin.driver_info'))
                ->schema([

                Forms\Components\FileUpload::make('profile_image')
                    ->label(__('strings.profile_image'))
                    ->image()
                    ->required(),

                Forms\Components\TextInput::make('first_name')
                    ->label(__('strings.first_name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('last_name')
                    ->label(__('strings.last_name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone_number')
                    ->label(__('strings.phone_number'))
                    ->tel()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label(__('strings.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label(__('strings.password'))
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->revealable()
                    ->maxLength(255),    

                Forms\Components\TextInput::make('id_number')
                    ->label(__('strings.id_number'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('date_of_birth')
                    ->label(__('strings.date_of_birth'))
                    ->required(),

                Forms\Components\TextInput::make('nationality')
                    ->label(__('strings.nationality'))
                    ->required()
                    ->maxLength(255),

                FileUpload::make('vehicle_registration_document')
                    ->label(__('strings.vehicle_registration_document'))
                    ->required(),

                FileUpload::make('vehicle_insurance_document')
                    ->label(__('strings.vehicle_insurance_document'))
                    ->required(),

                Forms\Components\TextInput::make('type_of_vehicle')
                    ->label(__('strings.type_of_vehicle'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('vehicle_model')
                    ->label(__('strings.vehicle_model'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('year_of_manufacture')
                    ->label(__('strings.year_of_manufacture'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('license_plate_number')
                    ->label(__('strings.license_plate_number'))
                    ->required()
                    ->maxLength(255),

                FileUpload::make('driving_license_photo')
                    ->label(__('strings.driving_license_photo'))
                    ->required(),

                Forms\Components\DatePicker::make('license_issue_date')
                    ->label(__('strings.license_issue_date'))
                    ->required(),

                Forms\Components\DatePicker::make('license_expiry_date')
                    ->label(__('strings.license_expiry_date'))
                    ->required(),

                Forms\Components\Toggle::make('previous_experience')
                    ->label(__('strings.previous_experience'))
                    ->required(),

                Forms\Components\Textarea::make('experience')
                    ->label(__('strings.experience'))
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->label(__('strings.city'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('district_area')
                    ->label(__('strings.district_area'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Toggle::make('have_gps')
                    ->label(__('strings.have_gps'))
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label(__('strings.notes'))
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('reject_reason')
                    ->label(__('strings.reject_reason'))
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->label(__('strings.status'))
                    ->options([
                        'accepted' => __('strings.accepted'),
                        'rejected' => __('strings.rejected'),
                        'pending'  => __('strings.pending'),
                    ])
                    ->default('pending')
                    ->required(),

                ])->columns(4),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')->label(__('strings.profile_image')),
                Tables\Columns\TextColumn::make('first_name')->label(__('strings.first_name'))->searchable(),
                Tables\Columns\TextColumn::make('last_name')->label(__('strings.last_name'))->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->label(__('strings.phone_number'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('strings.email'))->searchable(),
                Tables\Columns\TextColumn::make('id_number')->label(__('strings.id_number'))->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')->label(__('strings.date_of_birth'))->date()->sortable(),
                Tables\Columns\TextColumn::make('nationality')->label(__('strings.nationality'))->searchable(),
                Tables\Columns\TextColumn::make('vehicle_registration_document')->label(__('strings.vehicle_registration_document'))->searchable(),
                Tables\Columns\TextColumn::make('vehicle_insurance_document')->label(__('strings.vehicle_insurance_document'))->searchable(),
                Tables\Columns\TextColumn::make('type_of_vehicle')->label(__('strings.type_of_vehicle'))->searchable(),
                Tables\Columns\TextColumn::make('vehicle_model')->label(__('strings.vehicle_model'))->searchable(),
                Tables\Columns\TextColumn::make('year_of_manufacture')->label(__('strings.year_of_manufacture'))->searchable(),
                Tables\Columns\TextColumn::make('license_plate_number')->label(__('strings.license_plate_number'))->searchable(),
                Tables\Columns\TextColumn::make('driving_license_photo')->label(__('strings.driving_license_photo'))->searchable(),
                Tables\Columns\TextColumn::make('license_issue_date')->label(__('strings.license_issue_date'))->date()->sortable(),
                Tables\Columns\TextColumn::make('license_expiry_date')->label(__('strings.license_expiry_date'))->date()->sortable(),
                Tables\Columns\IconColumn::make('previous_experience')->label(__('strings.previous_experience'))->boolean(),
                Tables\Columns\TextColumn::make('city')->label(__('strings.city'))->searchable(),
                Tables\Columns\TextColumn::make('district_area')->label(__('strings.district_area'))->searchable(),
                Tables\Columns\IconColumn::make('have_gps')->label(__('strings.have_gps'))->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('strings.status'))
                    ->formatStateUsing(fn ($state) => [
                        'accepted' => __('strings.accepted'),
                        'rejected' => __('strings.rejected'),
                        'pending'  => __('strings.pending'),
                    ][$state] ?? $state),
              ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
               Action::make('accept_driver')
                ->label(__('strings.accepted'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (Driver $record): bool => $record->status !== 'accepted')
                ->requiresConfirmation()
                ->form([
                    Select::make('branch')
                        ->relationship('branches', 'name')
                        ->multiple()
                        ->preload()
                        ->label(__('strings.branches'))
                        ->required(),
                ])
                ->action(function (Driver $record, array $data) {
                    $record->update([
                        'status' => 'accepted',
                    ]);

                    if (isset($data['branch'])) {
                        $record->branches()->syncWithoutDetaching($data['branch']);
                    }

                    Notification::make()
                        ->title(__('strings.driver_accepted_assigned_to_branches'))
                        ->success()
                        ->send();
                }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        

        return parent::getEloquentQuery()->whereNotIn('status', ['in_complete']);
    }

    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
           // 'create' => Pages\CreateDriver::route('/create'),
            'view' => Pages\ViewDriver::route('/{record}'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
