<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MaterialConsumptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'materialConsumptions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.material_consumption_history');
    }

    public function form(Form $form): Form
    {
        // Read-only form - no creation or editing allowed
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
                Forms\Components\Select::make('status')
                    ->label(__('admin.status'))
                    ->options([
                        'sent' => __('admin.sent'),
                        'consumed' => __('admin.consumed'),
                    ])
                    ->disabled(),
                Forms\Components\DatePicker::make('transaction_date')
                    ->label(__('admin.transaction_date'))
                    ->disabled(),
                Forms\Components\DatePicker::make('sent_date')
                    ->label(__('admin.sent_date'))
                    ->disabled(),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('status', 'consumed')
            ->with(['material', 'order'])
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

                Tables\Columns\TextColumn::make('sent_date')
                    ->label(__('admin.sent_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('consumer_name')
                    ->label(__('admin.consumer'))
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
                // No edit or delete actions - records are read-only
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
