<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchMaterialShipmentResource\Pages;
use App\Models\BranchMaterialHistory;
use App\Models\BranchMaterialShipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchMaterialShipmentResource extends Resource
{
    protected static ?string $model = BranchMaterialShipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('admin.material_shipments_history');
    }

    public static function getModelLabel(): string
    {
        return __('admin.material_shipment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.material_shipments_history');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.branches_managment');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'sent')->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.shipment_information'))
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label(__('admin.branch'))
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset material when branch changes
                                $set('branch_material_id', null);
                                $set('material_id', null);
                                $set('unit', null);
                            }),

                        Forms\Components\Select::make('branch_material_id')
                            ->label(__('admin.material'))
                            ->relationship(
                                'branchMaterial',
                                'id',
                                fn ($query, $get) => $query->where('branch_id', $get('branch_id'))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->material->name ?? '')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $branchMaterial = \App\Models\BranchMaterial::find($state);
                                    if ($branchMaterial) {
                                        $set('material_id', $branchMaterial->material_id);
                                        $set('unit', $branchMaterial->unit);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('quantity')
                            ->label(__('admin.quantity'))
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix(fn ($get) => ' ' . strtoupper($get('unit') ?? '')),

                        Forms\Components\TextInput::make('unit')
                            ->label(__('admin.unit'))
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Hidden::make('material_id')
                            ->dehydrated(),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label(__('admin.shipment_date'))
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Set sent_date to transaction_date
                                $set('sent_date', $state);
                                $set('shipment_date', $state);
                            }),

                        Forms\Components\DatePicker::make('shipment_date')
                            ->label(__('admin.shipment_date'))
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->hidden(),

                        Forms\Components\Hidden::make('sent_date')
                            ->dehydrated(),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('admin.notes'))
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('admin.branch'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('material.name')
                    ->label(__('admin.material'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('material.code')
                    ->label(__('admin.code'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('admin.quantity'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->suffix(fn ($record) => ' ' . strtoupper($record->unit ?? ''))
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('unit')
                    ->label(__('admin.unit'))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label(__('admin.shipment_date'))
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.status'))
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (string $state): string => __('admin.sent'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label(__('admin.notes'))
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('admin.branch'))
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('material_id')
                    ->label(__('admin.material'))
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('transaction_date')
                    ->label(__('admin.shipment_date'))
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('admin.from_date')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('admin.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->emptyStateHeading(__('admin.no_shipments'))
            ->emptyStateDescription(__('admin.no_shipments_description'))
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranchMaterialShipments::route('/'),
            'create' => Pages\CreateBranchMaterialShipment::route('/create'),
            'edit' => Pages\EditBranchMaterialShipment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Use BranchMaterialShipment model which uses branch_material_shipments table
        // Filter by status = 'sent' for shipments
        return parent::getEloquentQuery()
            ->where('status', 'sent')
            ->with(['branch', 'material', 'branchMaterial']);
    }
}
