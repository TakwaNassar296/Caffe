<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\LocationsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

         public static function getNavigationLabel(): string
    {
        return __('strings.users');
    }

    public static function getModelLabel(): string
    {
        return __('strings.users');
    }

    public static function getPluralModelLabel(): string
    {
        return __('strings.users');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_employees_users');
    }

    public static function getNavigationBadge(): ?string
    {

        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('first_name')
                        ->label(__('strings.first_name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('last_name')
                        ->label(__('strings.last_name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('phone_number')
                        ->label(__('strings.phone_number'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->minLength(8)
                        ->maxLength(20),

                    DateTimePicker::make('account_verified_at')
                       ->label(__('strings.account_verified_at'))
                        ->required(),

                    TextInput::make('total_points')
                     ->label(__('strings.total_points'))
                        ->numeric()
                        ->default(0)
                        ->required(),

                    TextInput::make('total_stars')
                     ->label(__('strings.total_stars'))
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->label(__('strings.password'))
                        ->visibleOn('create')
                        ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                        ->minLength(8)
                        ->maxLength(255),

                    FileUpload::make('image')
                       ->label(__('strings.image'))
                        ->directory('profile_images')
                        ->image()
                        ->columnSpan('full')
                        ->nullable()
                        ->imageEditor()
                        ->maxSize(2048),


                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                ->label(__('strings.image'))
                ->circular(),
 
                TextColumn::make('first_name')
                ->label(__('strings.first_name'))
                ->searchable()
                ->sortable(),

                TextColumn::make('last_name')
                ->label(__('strings.last_name'))
                ->searchable()
                ->sortable(),

                TextColumn::make('phone_number')
                ->label(__('strings.phone_number'))
                ->searchable(),

                TextColumn::make('total_points')
                ->label(__('strings.total_points'))
                ->sortable(),

                TextColumn::make('total_stars')
                ->label(__('strings.total_stars'))
                ->sortable(),

                     Tables\Columns\TextColumn::make('account_verified_at')
                   ->label(__('strings.account_verified_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


                  Tables\Columns\TextColumn::make('created_at')
                   ->label(__('strings.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\ViewAction::make()->button(),
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
             LocationsRelationManager::class,
             OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

      public static function canViewAny(): bool
    {
        return optional(auth()->guard('admin')->user())->super_admin === 1;
    }
}
