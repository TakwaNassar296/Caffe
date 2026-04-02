<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchMaterialResource\Pages;
use App\Filament\Resources\BranchMaterialResource\RelationManagers;
use App\Models\BranchMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchMaterialResource extends Resource
{
    protected static ?string $model = BranchMaterial::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('strings.branches_materail');
    }

    public static function getModelLabel(): string
    {
        return __('strings.branches_materail');
    }

    public static function getPluralModelLabel(): string
    {
        return __('strings.branches_materail');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.materials');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.material_information'))
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label(__('admin.branch'))
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('material_id')
                            ->label(__('strings.material'))
                            ->relationship('material', 'name')
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, $get) => $rule->where('branch_id', $get('branch_id')))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('admin.stock_information'))
                    ->schema([
                        Forms\Components\TextInput::make('quantity_in_stock')
                            ->label(__('admin.quantity_in_stock'))
                            ->helperText(__('admin.quantity_sent_by_admin'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0.00)
                            ->suffix(fn ($get, $record) => $record?->unit ?? $get('unit') ?? '')
                            ->columnSpan(1),

                        Forms\Components\Select::make('unit')
                            ->label(__('admin.unit'))
                            ->required()
                            ->options([
                                'ml' => __('admin.ml'),
                                'l' => __('admin.l'),
                                'g' => __('admin.g'),
                                'kg' => __('admin.kg'),
                                'pcs' => __('admin.pcs'),
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('current_quantity')
                            ->label(__('admin.current_quantity'))
                            ->helperText(__('admin.quantity_already_used'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix(fn ($get, $record) => $record?->unit ?? $get('unit') ?? '')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('min_limit')
                            ->label(__('admin.min_limit'))
                            ->helperText(__('admin.minimum_allowed_limit'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            // ->suffix(fn ($get, $record) => $record?->unit ?? $get('unit') ?? '')
                             ,

                        Forms\Components\TextInput::make('max_limit')
                            ->label(__('admin.max_limit'))
                            ->helperText(__('admin.maximum_allowed_limit'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            // ->suffix(fn ($get, $record) => $record?->unit ?? $get('unit') ?? '')
                            ,

                        Forms\Components\Placeholder::make('remaining_quantity')
                            ->label(__('admin.remaining_quantity'))
                            ->content(function ($record, $get) {
                                 if (! $record) {
                                    $currentQuantity = (float) ($get('current_quantity') ?? 0);
                                    $unit = $get('unit') ?? '';

                                    if ($currentQuantity <= 0) {
                                        return '-';
                                    }

                                    return number_format($currentQuantity, 2) . ' ' . self::formatUnit($unit);
                                }

                                $remaining = $record->remaining_quantity;
                                $unit = $record->unit ?? '';
                                return number_format($remaining, 2) . ' ' . self::formatUnit($unit);
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('admin.branch'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('material.name')
                    ->label(__('strings.material'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('quantity_in_stock')
                    ->label(__('admin.quantity_in_stock'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->suffix(fn ($record) => ' ' . self::formatUnit($record->unit)),

                Tables\Columns\TextColumn::make('current_quantity')
                    ->label(__('admin.current_quantity'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->suffix(fn ($record) => ' ' . self::formatUnit($record->unit))
                    ->default(__('admin.not_set')),

                Tables\Columns\TextColumn::make('remaining_quantity')
                    ->label(__('admin.remaining_quantity'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color(fn ($record) => $record->remaining_quantity > 0 ? 'success' : 'danger')
                    ->suffix(fn ($record) => ' ' . self::formatUnit($record->unit))
                    ->getStateUsing(fn ($record) => $record->remaining_quantity),

                Tables\Columns\TextColumn::make('min_limit')
                    ->label(__('admin.min_limit'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->suffix(fn ($record) => ' ' . self::formatUnit($record->unit))
                    ->toggleable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('max_limit')
                    ->label(__('admin.max_limit'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->suffix(fn ($record) => ' ' . self::formatUnit($record->unit))
                    ->toggleable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('unit')
                    ->label(__('admin.unit'))
                    ->formatStateUsing(fn ($state) => self::formatUnit($state))
                    ->badge()
                    ->toggleable(),

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
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('admin.branch')),

                Tables\Filters\SelectFilter::make('unit')
                    ->options([
                        'ml' => __('admin.ml'),
                        'l' => __('admin.l'),
                        'g' => __('admin.g'),
                        'kg' => __('admin.kg'),
                        'pcs' => __('admin.pcs'),
                    ])
                    ->label(__('admin.unit')),

                Tables\Filters\Filter::make('low_stock')
                    ->label(__('admin.low_stock'))
                    ->query(fn (Builder $query) => $query->whereColumn('current_quantity', '<', 'quantity_in_stock')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.no_materials'))
            ->emptyStateDescription(__('admin.no_materials_description'))
            ->emptyStateIcon('heroicon-o-cube');
    }

    protected static function formatUnit(string $unit): string
    {
        return match ($unit) {
            'ml' => __('admin.ml'),
            'l' => __('admin.l'),
            'g' => __('admin.g'),
            'kg' => __('admin.kg'),
            'pcs' => __('admin.pcs'),
            default => $unit,
        };
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ShipmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranchMaterials::route('/'),
            'create' => Pages\CreateBranchMaterial::route('/create'),
            'view' => Pages\ViewBranchMaterial::route('/{record}'),
            'edit' => Pages\EditBranchMaterial::route('/{record}/edit'),
        ];
    }
}