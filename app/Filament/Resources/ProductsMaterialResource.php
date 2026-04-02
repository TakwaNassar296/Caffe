<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductsMaterial;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use App\Filament\Resources\ProductsMaterialResource\Pages;

class ProductsMaterialResource extends Resource
{
    protected static ?string $model = ProductsMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('admin.preparation_materials_products');
    }

    public static function getModelLabel(): string
    {
        return __('admin.preparation_materials_products');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.preparation_materials_products');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.products_category');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->schema([

                    Forms\Components\Select::make('product_id')
                        ->label(__('strings.product'))
                        ->relationship('product', 'name')
                        ->required()->searchable()->preload(),

                             Forms\Components\Select::make('product_option_id')
                                ->label(__('strings.product_option'))
                                ->relationship('productOption', 'name')
                                ->nullable()->searchable()->preload(),

                                

                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('material_id')
                                ->label(__('strings.select_material'))
                                ->relationship('material', 'name')
                                ->live()
                                ->reactive()
                                ->required()
                                ->afterStateUpdated(function ($state, Set $set) {

                                    if ($state) {
                                        $material = \App\Models\Material::find($state);
                                        if ($material) {
                                            $set('unit', $material->unit);
                                        }
                                    } else {
                                        $set('unit', null);
                                    }
                                }),

                       

                            Forms\Components\TextInput::make('quantity_used')
                                ->label(__('strings.quantity_used'))
                                ->numeric()
                                ->required(),

                            Forms\Components\Select::make('unit')
                                ->label(__('admin.unit'))
                                ->required()
                                ->options(
                                fn(Get $get) => $get('unit')
                                    ? [$get('unit') => __("admin." . $get('unit'))]
                                    : []
                            )
                                ->native(false),
                        ])
                        ->columns(4)
                ]),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')
                ->label(__('strings.product'))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('productOption.name')
                ->label(__('strings.product_option'))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('items.material.name') 
                ->label(__('strings.material'))
                ->listWithLineBreaks(),

            Tables\Columns\TextColumn::make('items.quantity_used')
                ->label(__('strings.quantity_used'))
                ->listWithLineBreaks(),

            Tables\Columns\TextColumn::make('items.unit')
                ->label(__('strings.unit'))
                ->listWithLineBreaks()
                ->formatStateUsing(fn (string $state): string => __("admin.{$state}")),


        ])
            ->actions([
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductsMaterials::route('/'),
            'create' => Pages\CreateProductsMaterial::route('/create'),
            'view'   => Pages\ViewProductsMaterial::route('/{record}'),
            'edit'   => Pages\EditProductsMaterial::route('/{record}/edit'),
        ];
    }
}
