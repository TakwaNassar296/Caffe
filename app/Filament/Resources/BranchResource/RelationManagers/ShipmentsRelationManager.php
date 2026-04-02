<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shipments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.material_shipments_history');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_material_id')
                    ->label(__('admin.material'))
                    ->relationship(
                        'branchMaterial',
                        'id',
                        function (Builder $query) {
                            return $query->where('branch_id', $this->ownerRecord->id);
                        }
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->material->name ?? '')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn ($record) => $record !== null)
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('transaction_date')
                    ->label(__('admin.shipment_date'))
                    ->required()
                    ->default(now())
                    ->displayFormat('Y-m-d')
                    ->columnSpan(1),

                Forms\Components\TextInput::make('quantity')
                    ->label(__('admin.quantity'))
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->suffix(function ($get, $record) {
                        if ($record && $record->branchMaterial) {
                            return $record->branchMaterial->unit ?? '';
                        }
                        $branchMaterialId = $get('branch_material_id');
                        if ($branchMaterialId) {
                            $branchMaterial = \App\Models\BranchMaterial::find($branchMaterialId);
                            return $branchMaterial->unit ?? '';
                        }
                        return '';
                    })
                    ->helperText(__('admin.shipment_quantity'))
                    ->columnSpan(1),

                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.notes'))
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['branchMaterial.material', 'branchMaterial.material.category']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_date')
            ->columns([
                Tables\Columns\TextColumn::make('branchMaterial.material.name')
                    ->label(__('admin.material_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('branchMaterial.material.code')
                    ->label(__('admin.code'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('admin.quantity'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->suffix(fn ($record) => ' ' . ($record->branchMaterial->unit ?? '')),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label(__('admin.shipment_date'))
                    ->date()
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('material')
                    ->relationship('branchMaterial.material', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('admin.material')),

                Tables\Filters\Filter::make('shipment_date')
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set branch_material_id if not set
                        if (!isset($data['branch_material_id'])) {
                            // Get first branch material for this branch
                            $branchMaterial = \App\Models\BranchMaterial::where('branch_id', $this->ownerRecord->id)->first();
                            if ($branchMaterial) {
                                $data['branch_material_id'] = $branchMaterial->id;
                            }
                        }
                        $data['quantity'] = (float) ($data['quantity'] ?? 0);
                        $data['status'] = 'sent';
                        $data['branch_id'] = $this->ownerRecord->id;
                        $data['material_id'] = \App\Models\BranchMaterial::find($data['branch_material_id'])->material_id ?? null;
                        $data['unit'] = \App\Models\BranchMaterial::find($data['branch_material_id'])->unit ?? null;
                        $data['transaction_date'] = $data['transaction_date'] ?? now();
                        $data['sent_date'] = $data['transaction_date'];
                        $data['consumer_type'] = 'branch';
                        $data['consumer_name'] = $this->ownerRecord->name ?? 'Branch';
                        return $data;
                    })
                    ->after(function ($record) {
                        // Update branch_material quantity_in_stock by adding shipment quantity
                        $branchMaterial = $record->branchMaterial;
                        if ($branchMaterial) {
                            $branchMaterial->quantity_in_stock = ($branchMaterial->quantity_in_stock ?? 0) + $record->quantity;
                            $branchMaterial->save();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        $oldQuantity = $record->quantity;
                        $data['old_quantity'] = $oldQuantity;
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        // Update branch_material quantity_in_stock based on quantity difference
                        $branchMaterial = $record->branchMaterial;
                        if ($branchMaterial && isset($data['old_quantity'])) {
                            $quantityDiff = $data['quantity'] - $data['old_quantity'];
                            $branchMaterial->quantity_in_stock = max(0, ($branchMaterial->quantity_in_stock ?? 0) + $quantityDiff);
                            $branchMaterial->save();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        // Deduct shipment quantity from branch_material quantity_in_stock
                        $branchMaterial = $record->branchMaterial;
                        if ($branchMaterial) {
                            $branchMaterial->quantity_in_stock = max(0, ($branchMaterial->quantity_in_stock ?? 0) - $record->quantity);
                            $branchMaterial->save();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function ($records) {
                            // Deduct all deleted shipment quantities from branch_material
                            foreach ($records as $record) {
                                $branchMaterial = $record->branchMaterial;
                                if ($branchMaterial) {
                                    $branchMaterial->quantity_in_stock = max(0, ($branchMaterial->quantity_in_stock ?? 0) - $record->quantity);
                                    $branchMaterial->save();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->emptyStateHeading(__('admin.no_shipments'))
            ->emptyStateDescription(__('admin.no_shipments_description'))
            ->emptyStateIcon('heroicon-o-truck');
    }
}
