<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\UserLocationRelationManager;
use App\Filament\Resources\OrderResource\Widgets\StatsOrder;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationLabel(): string
    {
        return __('strings.orders');
    }

    public static function getModelLabel(): string
    {
        return __('strings.orders');
    }

    public static function getPluralModelLabel(): string
    {
        return __('strings.orders');
    }
    
    public static function getNavigationGroup(): string
    {
        return __('strings.orders');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->guard('admin')->user();
        if ($user?->super_admin == 1) {
            return Order::count();
        }

        return Order::where('branch_id', $user->branch->id)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__(__('strings.branch_name')))
                    ->visible(fn() => auth()->guard('admin')->user()->super_admin == 1)
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.first_name')
                    ->label(__('strings.first_name'))
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('strings.total_price'))
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label(__('strings.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'under_receipt' => 'warning',
                        'under_review' => 'warning',
                        'in_preparation' => 'warning',
                        'prepared' => 'warning',
                        'shipped' => 'warning',
                        'arrived' => 'warning',
                        'canceled' => 'danger',
                        'completed' => 'success',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => __('strings.' . $state)),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label(__('strings.type')),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('strings.status'))
                    ->options([
                        'pending'         => __('strings.pending'),
                        'under_receipt'   => __('strings.under_receipt'),
                        'under_review'    => __('strings.under_review'),
                        'in_preparation'  => __('strings.in_preparation'),
                        'prepared'        => __('strings.prepared'),
                        'shipped'         => __('strings.shipped'),
                        'arrived'         => __('strings.arrived'),
                        'completed'       => __('strings.completed'),
                        'canceled'        => __('strings.canceled'),
                    ]),
                 
                SelectFilter::make('branch_id')
                    ->visible(fn() => auth()->guard('admin')->user()->super_admin == 1)
                    ->label(__(__('strings.branch_name')))
                    ->options(fn () => \App\Models\Branch::pluck('name', 'id')->toArray())
                    ->searchable(), 
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Action::make('changeStatus')
                    ->label(__('strings.change_status'))
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary')
                    ->modalHeading(__('strings.change_status'))
                    ->modalSubmitActionLabel(__('strings.save'))
                    ->modalWidth('md')
                    ->form([
                        Select::make('status')
                            ->label(__('strings.status'))
                            ->required()
                            ->default(fn ($record) => $record->status)
                            ->options([
                                'pending'        => __('strings.pending'),
                                'under_receipt'  => __('strings.under_receipt'),
                                'under_review'   => __('strings.under_review'),
                                'in_preparation' => __('strings.in_preparation'),
                                'prepared'       => __('strings.prepared'),
                                'shipped'        => __('strings.shipped'),
                                'arrived'        => __('strings.arrived'),
                                'completed'      => __('strings.completed'),
                                'canceled'       => __('strings.canceled'),
                            ])
                            ->live()
                            ->native(false),

                        Textarea::make('cancelled_reason')
                            ->label(__('strings.cancel_reason'))
                            ->rows(3)
                            ->visible(fn($get) => $get('status') === 'canceled')
                            ->required(fn($get) => $get('status') === 'canceled')
                            ->maxLength(500),
                    ])
                    ->action(function (array $data, Order $record) {
                        try {
                            // Update the order
                            $record->status = $data['status'];
                            
                            if ($data['status'] === 'canceled') {
                                $record->cancelled_reason = $data['cancelled_reason'];
                            } else {
                                $record->cancelled_reason = null;
                            }
                            
                            $record->save();

                            // Send success notification
                            Notification::make()
                                ->title(__('strings.order_status_updated_successfully'))
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            // Send error notification
                            Notification::make()
                                ->title(__('strings.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            StatsOrder::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $auth = auth()->guard('admin')->user();

        if ($auth->super_admin == 1) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('branch_id', $auth->branch->id);
    }
   
}