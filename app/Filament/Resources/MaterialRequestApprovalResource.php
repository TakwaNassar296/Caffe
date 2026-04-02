<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialRequestApprovalResource\Pages;
use App\Models\MaterialRequestApproval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialRequestApprovalResource extends Resource
{
    protected static ?string $model = MaterialRequestApproval::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function getNavigationLabel(): string
    {
        return __('admin.approval_history_material');
    }

    public static function getModelLabel(): string
    {
        return __('admin.approval_history_material');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.approval_history_material');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.branches_managment');
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getEloquentQuery(): Builder
    {
        $auth = auth()->guard('admin')->user();
        
        if ($auth && $auth->super_admin == 1) {
            return parent::getEloquentQuery()
                ->with(['requestMaterial.material', 'requestMaterial.branch', 'admin']);
        }

        // Branch admin can only see approvals for their branch requests
        return parent::getEloquentQuery()
            ->whereHas('requestMaterial', function ($query) use ($auth) {
                $query->where('branch_id', $auth->branch_id);
            })
            ->with(['requestMaterial.material', 'requestMaterial.branch', 'admin']);
    }

    public static function form(Form $form): Form
    {
        $auth = auth()->guard('admin')->user();
        $isSuperAdmin = $auth && $auth->super_admin == 1;

        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.approval_information'))
                    ->schema([
                        Forms\Components\Select::make('request_material_id')
                            ->label(__('admin.material_request'))
                            ->relationship('requestMaterial', 'id')
                            ->disabled()
                            ->dehydrated()
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                'Request #' . $record->id
                            ),

                        Forms\Components\Select::make('admin_id')
                            ->label(__('admin.reviewed_by'))
                            ->relationship('admin', 'name')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('action')
                            ->label(__('admin.action'))
                            ->options([
                                'approved' => __('admin.approved'),
                                'rejected' => __('admin.rejected'),
                                'updated' => __('admin.updated'),
                            ])
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('quantity')
                            ->label(__('admin.quantity_in_stock'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('comment')
                            ->label(__('admin.description'))
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $auth = auth()->guard('admin')->user();
        $isSuperAdmin = $auth && $auth->super_admin == 1;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requestMaterial.branch.name')
                    ->label(__('admin.branch'))
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdmin),

                Tables\Columns\TextColumn::make('requestMaterial.branch.phone_number')
                    ->label(__('admin.mobile'))
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdmin),

                Tables\Columns\TextColumn::make('requestMaterial.material.name')
                    ->label(__('admin.material_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('requestMaterial.quantity')
                    ->label(__('admin.requested_quantity'))
                    ->numeric()
                    ->formatStateUsing(fn ($record) => 
                        $record->requestMaterial->quantity . ' ' . 
                        ($record->requestMaterial->material->unit ?? '')
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label(__('admin.action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'updated' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('admin.approved_quantity'))
                    ->numeric()
                    ->formatStateUsing(fn ($record, $state) => 
                        $state ? $state . ' ' . ($record->requestMaterial->material->unit ?? '') : '-'
                    ),

                Tables\Columns\TextColumn::make('admin.name')
                    ->label(__('admin.reviewed_by'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label(__('admin.description'))
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->comment),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.date_time'))
                    ->dateTime()
                    ->sortable(),
                 
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label(__('admin.action'))
                    ->options([
                        'approved' => __('admin.approved'),
                        'rejected' => __('admin.rejected'),
                        'updated' => __('admin.updated'),
                    ]),

                SelectFilter::make('requestMaterial.branch_id')
                    ->label(__('admin.branch'))
                    ->relationship('requestMaterial.branch', 'name')
                    ->visible($isSuperAdmin)
                    ->searchable(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })->columns(2),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->bulkActions([
                // No bulk actions for read-only history
            ]);
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
            'index' => Pages\ListMaterialRequestApprovals::route('/'),
            'view' => Pages\ViewMaterialRequestApproval::route('/{record}'),
        ];
    }
}
