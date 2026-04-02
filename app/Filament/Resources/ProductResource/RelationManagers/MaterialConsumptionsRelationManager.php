<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Database\Eloquent\Model;

class MaterialConsumptionsRelationManager extends RelationManager
{
    // Custom relationship - not using standard Eloquent relationship
    protected static string $relationship = 'materialConsumptions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.material_consumption_history');
    }

    public function form(Form $form): Form
    {
        // Read-only form
        return $form
            ->schema([
                Forms\Components\TextInput::make('material.name')
                    ->label(__('admin.material'))
                    ->disabled(),
                Forms\Components\TextInput::make('quantity')
                    ->label(__('admin.total_quantity_consumed'))
                    ->disabled(),
                Forms\Components\TextInput::make('unit')
                    ->label(__('admin.unit'))
                    ->disabled(),
                Forms\Components\DatePicker::make('transaction_date')
                    ->label(__('admin.consumed_date'))
                    ->disabled(),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        // Get all material IDs used in this product
        $product = $this->ownerRecord;
        $materialIds = $product->productsMaterials()
            ->with('items.material')
            ->get()
            ->flatMap(function ($productMaterial) {
                return $productMaterial->items->pluck('material.id')->filter();
            })
            ->unique()
            ->toArray();

        if (empty($materialIds)) {
           
            return \App\Models\BranchMaterialHistory::query()->whereRaw('1 = 0');
        }
 
        return \App\Models\BranchMaterialHistory::query()
            ->whereIn('material_id', $materialIds)
            ->where('status', 'consumed')
            ->whereHas('order.items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->with(['material', 'branch', 'order'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_date')
            ->columns([
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
                    ->label(__('admin.total_quantity_consumed'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->suffix(fn ($record) => ' ' . strtoupper($record->unit ?? ''))
                    ->weight('bold')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('unit')
                    ->label(__('admin.unit'))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label(__('admin.consumed_date'))
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('admin.branch'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sent_date')
                    ->label(__('admin.sent_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->default('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('order.order_num')
                    ->label(__('admin.order_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('material_id')
                    ->label(__('admin.material'))
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('admin.branch'))
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->toggleable(),

                Tables\Filters\Filter::make('transaction_date')
                    ->label(__('admin.consumed_date'))
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
                // No create action - records are created automatically
            ])
            ->actions([
                // Read-only - only view action
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions - records cannot be deleted
            ])
            ->defaultSort('transaction_date', 'desc')
            ->emptyStateHeading(__('admin.no_consumption_records'))
            ->emptyStateDescription(__('admin.no_consumption_records_description'))
            ->emptyStateIcon('heroicon-o-cube');
    }
}
