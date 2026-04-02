<?php

namespace App\Filament\Resources\RequestMaterialResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only form for approval history
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('admin.name')
                    ->label(__('Reviewed By'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label(__('Action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'updated' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->formatStateUsing(fn ($record, $state) => $state ? $state . ' ' . ($record->requestMaterial->material->unit ?? '') : '-'),

                Tables\Columns\TextColumn::make('comment')
                    ->label(__('Comment'))
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->comment),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date & Time'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label(__('Action'))
                    ->options([
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        'updated' => __('Updated'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                // No create action - approvals are created via approve/reject actions
            ])
            ->actions([
                // Read-only view
            ])
            ->bulkActions([
                // No bulk actions
            ]);
    }
}
