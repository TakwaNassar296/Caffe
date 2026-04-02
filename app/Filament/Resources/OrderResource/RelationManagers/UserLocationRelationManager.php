<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class UserLocationRelationManager extends RelationManager
{
    protected static string $relationship = 'userLocation';

      public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('strings.user_details');
    }

    public static function getModelLabel(): string
    {
        return __('strings.user_details');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                 Tables\Columns\TextColumn::make('first_name')->label(__('strings.first_name')),
                Tables\Columns\TextColumn::make('last_name')->label(__('strings.last_name')),
                Tables\Columns\TextColumn::make('phone_number')->label(__('strings.phone_number')),
                Tables\Columns\TextColumn::make('address_title')->label(__('strings.address_title')),
                Tables\Columns\TextColumn::make('name_address')->label(__('strings.name_address')),
                Tables\Columns\TextColumn::make('building_number')->label(__('strings.building_number')),
                Tables\Columns\TextColumn::make('floor')->label(__('strings.floor')),
                Tables\Columns\TextColumn::make('apartment')->label(__('strings.apartment')),
                Tables\Columns\TextColumn::make('address_description')->label(__('strings.address_description')),
                // Tables\Columns\TextColumn::make('latitude')->label(__('strings.latitude')),
                // Tables\Columns\TextColumn::make('longitude')->label(__('strings.longitude'))
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
