<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    public static function getNavigationLabel(): string
    {
        return __('admin.cities');
    }

    public static function getModelLabel(): string
    {
        return __('admin.city');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.city');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_branches_cities');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('strings.city_details'))
                    ->schema([
                        Forms\Components\Select::make('governorate_id')
                            ->label(__('strings.governorate'))
                            ->relationship('governorate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label(__('strings.name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label(__('strings.code'))
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(999999)
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        // Forms\Components\Toggle::make('is_active')
                        //     ->label(__('admin.is_active'))
                        //     ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('governorate.name')
                    ->label(__('strings.governorate'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('strings.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('strings.code'))
                    
                    ->searchable(),

                // Tables\Columns\ToggleColumn::make('is_active')
                //     ->label(__('admin.is_active')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('governorate_id')
                    ->label(__('strings.governorate'))
                    ->relationship('governorate', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(__('strings.view')),
                Tables\Actions\EditAction::make()->label(__('strings.edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('strings.delete')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'view' => Pages\ViewCity::route('/{record}'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
