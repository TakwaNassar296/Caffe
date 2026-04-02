<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_active')
                ->required(),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('points')
                ->numeric(),
            Forms\Components\TextInput::make('stars')
                ->numeric(),
            Forms\Components\TextInput::make('title')
                ->maxLength(255),
            Forms\Components\TextInput::make('price')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('coupon_id')
                ->numeric(),
            Forms\Components\TextInput::make('category_id')
                ->numeric(),
            Forms\Components\TextInput::make('rating')
                ->numeric(),
            Forms\Components\TextInput::make('total_rating')
                ->numeric(),
            Forms\Components\TextInput::make('amount')
                ->numeric(),
            Forms\Components\TextInput::make('delivery_time')
                ->maxLength(255),
            Forms\Components\FileUpload::make('image')
                ->image(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('points')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('stars')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_sales')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('price')->money()->sortable(),
                Tables\Columns\TextColumn::make('coupon_id')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('category_id')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('rating')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_rating')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('amount')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('remaining_quantity')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('delivery_time')->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
