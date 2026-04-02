<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestMaterialResource\Pages;
use App\Filament\Resources\RequestMaterialResource\RelationManagers\ApprovalsRelationManager;
use App\Models\RequestMaterial;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RequestMaterialResource extends Resource
{
    protected static ?string $model = RequestMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function getNavigationLabel(): string
    {
        return __('admin.material_requests');
    }

    public static function getModelLabel(): string
    {
        return __('admin.material_request');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.material_requests');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.branches_managment');
    }

    public static function getNavigationBadge(): ?string
    {
        $auth = auth()->guard('admin')->user();
        if ($auth && $auth->super_admin == 1) {
            return static::getModel()::where('status', 'pending')->count();
        }
        return null;
    }

    public static function getEloquentQuery(): Builder
    {
        $auth = auth()->guard('admin')->user();
        
        if ($auth && $auth->super_admin == 1) {
            return parent::getEloquentQuery()
                ->with(['branch', 'material', 'approvals.admin']);
        }

        return parent::getEloquentQuery()
            ->where('branch_id', $auth->branch_id)
            ->with(['branch', 'material', 'approvals.admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.request_information'))
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label(__('admin.branch'))
                            ->relationship('branch', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('material_id')
                            ->label(__('admin.material_name'))
                            ->relationship('material', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('quantity')
                            ->label(__('admin.requested_quantity'))
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('status')
                            ->label(__('admin.status'))
                            ->options([
                                'pending' => __('admin.pending'),
                                'approved' => __('admin.approved'),
                                'partially_approved' => __('admin.partially_approved'),
                                'rejected' => __('admin.rejected'),
                            ])
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('approved_quantity')
                            ->label(__('admin.approved_quantity'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('comment')
                            ->label(__('admin.description'))
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.delivery_confirmation'))
                    ->schema([
                        Forms\Components\Select::make('delivery_status')
                            ->label(__('admin.delivery_status'))
                            ->options([
                                'pending' => __('admin.delivery_pending'),
                                'delivered' => __('admin.delivered'),
                                'not_delivered' => __('admin.not_delivered'),
                                'accept' => __('admin.accept'),
                                'reject' => __('admin.reject'),
                            ])
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('delivery_feedback')
                            ->label(__('admin.delivery_feedback'))
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('delivery_confirmed_at')
                            ->label(__('admin.delivery_confirmed_at'))
                            ->disabled()
                            ->dehydrated()
                            ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('Y-m-d H:i:s') : '-'),
                    ])->columns(2)
                    ->visible(fn ($record) => $record && in_array($record->status, ['approved', 'partially_approved'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        $auth = auth()->guard('admin')->user();
        $isSuperAdmin = $auth && $auth->super_admin == 1;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('admin.branch'))
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdmin),

                Tables\Columns\TextColumn::make('branch.phone_number')
                    ->label(__('admin.mobile'))
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdmin),

                Tables\Columns\TextColumn::make('material.name')
                    ->label(__('admin.material_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('admin.requested_quantity'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->quantity . ' ' . ($record->material->unit ?? '')),

                Tables\Columns\TextColumn::make('approved_quantity')
                    ->label(__('admin.approved_quantity'))
                    ->numeric()
                    ->formatStateUsing(fn ($record, $state) => $state ? $state . ' ' . ($record->material->unit ?? '') : '-'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'partially_approved' => 'info',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('delivery_status')
                    ->label(__('admin.delivery_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'delivered' => 'success',
                        'not_delivered' => 'danger',
                        'accept' => 'success',
                        'reject' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('admin.delivery_pending'),
                        'delivered' => __('admin.delivered'),
                        'not_delivered' => __('admin.not_delivered'),
                        'accept' => __('admin.accept'),
                        'reject' => __('admin.reject'),
                        default => $state,
                    })
                    ->visible(fn ($record) => $record && in_array($record->status ?? null, ['approved', 'partially_approved'])),

                Tables\Columns\TextColumn::make('latestApproval.admin.name')
                    ->label(__('admin.last_reviewed_by'))
                    ->visible($isSuperAdmin)
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.requested_at'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options([
                        'pending' => __('admin.pending'),
                        'approved' => __('admin.approved'),
                        'partially_approved' => __('admin.partially_approved'),
                        'rejected' => __('admin.rejected'),
                    ]),

                SelectFilter::make('branch_id')
                    ->label(__('admin.branch'))
                    ->relationship('branch', 'name')
                    ->visible($isSuperAdmin)
                    ->searchable(),

                SelectFilter::make('delivery_status')
                    ->label(__('admin.delivery_status'))
                    ->options([
                        'pending' => __('admin.delivery_pending'),
                        'delivered' => __('admin.delivered'),
                        'not_delivered' => __('admin.not_delivered'),
                        'accept' => __('admin.accept'),
                        'reject' => __('admin.reject'),
                    ])
                    ->visible(fn () => $isSuperAdmin),
                    ], layout: FiltersLayout::AboveContent)

            ->actions([
                Tables\Actions\ViewAction::make(),

                // Approve Action
                Action::make('approve')
                    ->label(__('admin.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $isSuperAdmin && $record && $record->status === 'pending')
                    ->modalHeading(__('admin.approve_material_request'))
                    ->modalSubmitActionLabel(__('admin.approve'))
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('admin.approved_quantity'))
                            ->numeric()
                            ->required()
                            ->default(fn ($record) => $record->quantity)
                            ->minValue(0.01)
                            ->maxValue(fn ($record) => $record->quantity)
                            ->helperText(fn ($record) => __('admin.maximum', ['max' => $record->quantity ?? 0])),

                        Textarea::make('comment')
                            ->label(__('admin.description'))
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (array $data, RequestMaterial $record) {
                        DB::beginTransaction();
                        try {
                            $adminId = auth()->guard('admin')->id();
                            $approvedQty = (float) $data['quantity'];
                            $requestedQty = (float) $record->quantity;

                            // Determine status
                            $status = 'approved';
                            if ($approvedQty < $requestedQty) {
                                $status = 'partially_approved';
                            }

                            // Update request
                            $record->update([
                                'status' => $status,
                                'approved_quantity' => $approvedQty,
                            ]);

                            // Create approval history
                            \App\Models\MaterialRequestApproval::create([
                                'request_material_id' => $record->id,
                                'admin_id' => $adminId,
                                'action' => $approvedQty < $requestedQty ? 'updated' : 'approved',
                                'quantity' => $approvedQty,
                                'comment' => $data['comment'] ?? null,
                            ]);

                            DB::commit();

                            Notification::make()
                                ->title(__('admin.request_approved_successfully'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title(__('admin.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Reject Action
                Action::make('reject')
                    ->label(__('admin.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $isSuperAdmin && $record && $record->status === 'pending')
                    ->modalHeading(__('admin.reject_material_request'))
                    ->modalSubmitActionLabel(__('admin.reject'))
                    ->form([
                        Textarea::make('comment')
                            ->label(__('admin.rejection_reason'))
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText(__('admin.please_provide_rejection_reason')),
                    ])
                    ->action(function (array $data, RequestMaterial $record) {
                        DB::beginTransaction();
                        try {
                            $adminId = auth()->guard('admin')->id();

                            // Update request
                            $record->update([
                                'status' => 'rejected',
                                'approved_quantity' => null,
                            ]);

                            // Create approval history
                            \App\Models\MaterialRequestApproval::create([
                                'request_material_id' => $record->id,
                                'admin_id' => $adminId,
                                'action' => 'rejected',
                                'quantity' => null,
                                'comment' => $data['comment'],
                            ]);

                            DB::commit();

                            Notification::make()
                                ->title(__('admin.request_rejected'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title(__('admin.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk actions can be added here if needed
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ApprovalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequestMaterials::route('/'),
            'view' => Pages\ViewRequestMaterial::route('/{record}'),
        ];
    }
}
