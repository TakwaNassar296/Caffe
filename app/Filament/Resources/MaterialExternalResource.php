<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialExternalResource\Pages;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialExternalResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    public static function getNavigationLabel(): string
    {
        return __('admin.external_materials');
    }

    public static function getModelLabel(): string
    {
        return __('admin.external_material');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.external_materials');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.materials');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('material_type', 'external')->count();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('material_type', 'external');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.add_materail'))
                ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('quantity_in_stock')
                    ->label(__('admin.quantity_in_stock'))
                    ->required()
                    ->numeric()
                    ->default(0.00)
                    ->reactive(),

                Forms\Components\Select::make('unit')
                    ->label(__('admin.unit'))
                    ->required()
                    ->options([
                        'ml'  => __('admin.ml'),
                        'l'   => __('admin.l'),
                        'g'   => __('admin.g'),
                        'kg'  => __('admin.kg'),
                        'pcs' => __('admin.pcs'),
                    ])
                    ->native(false),

                Forms\Components\Select::make('category_id')
                    ->label(__('admin.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\FileUpload::make('image')
                    ->label(__('admin.image'))
                    ->image()
                    ->directory('uploads/materials')
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->label(__('admin.status'))
                    ->options([
                        'low_stock' => __('admin.low_stock'),
                        'good' => __('admin.good'),
                        'out_of_stock' => __('admin.out_of_stock'),
                    ])
                    ->default('good')
                    ->required(),

                Forms\Components\Hidden::make('material_type')
                    ->default('external'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.image'))
                    ->circular()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.code'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity_in_stock')
                    ->label(__('admin.quantity_in_stock'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label(__('admin.unit'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? '')),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.status'))
                    ->colors([
                        'danger' => 'out_of_stock',
                        'warning' => 'low_stock',
                        'success' => 'good',
                    ])
                    ->formatStateUsing(fn ($state) => __("admin.{$state}"))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options([
                        'low_stock' => __('admin.low_stock'),
                        'good' => __('admin.good'),
                        'out_of_stock' => __('admin.out_of_stock'),
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('admin.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
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
            'index'  => Pages\ListMaterialExternals::route('/'),
            'create' => Pages\CreateMaterialExternal::route('/create'),
            'view'   => Pages\ViewMaterialExternal::route('/{record}'),
            'edit'   => Pages\EditMaterialExternal::route('/{record}/edit'),
        ];
    }
}
