<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('strings.order_details');
    }

    public static function getModelLabel(): string
    {
        return __('strings.order_details');
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

                 TextColumn::make('id')
                    ->label('id'),
                    
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Qty'),

                // TextColumn::make('price')
                //     ->money('EGP', true)
                //     ->label('Price'),

                TextColumn::make('total_price')
                    ->label('Total'),

                TextColumn::make('optionValues')
                    ->label('Options')
                    ->formatStateUsing(function ($record) {
                        return ($record->optionValues ?? collect())
                            ->map(fn($pv) => $pv->productOption?->name . ': ' . $pv->value)
                            ->implode(' | ');
                    }),
            ])
            ->defaultSort('id', 'desc')
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

