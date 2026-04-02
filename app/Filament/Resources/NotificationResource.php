<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationLabel(): string
    {
        return __('admin.notifications_system');
    }

    public static function getModelLabel(): string
    {
        return __('admin.notification_system');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.notifications_system');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.notifications');
    }

     public static function getNavigationBadge(): ?string
    {
             return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->label(__('admin.type'))
                    ->disabled(),
                Forms\Components\Textarea::make('data')
                    ->label(__('admin.data'))
                    ->disabled()
                    ->formatStateUsing(fn ($state) => is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data.title')
                    ->label(__('admin.title'))
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data.message')
                    ->label(__('admin.message'))
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('data.order_id')
                    ->label(__('strings.order_id'))
                    ->formatStateUsing(fn ($state) => $state ? '#' . $state : '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('read_at')
                    ->label(__('admin.read'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('unread')
                    ->label(__('admin.unread_notifications'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('read_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_as_read')
                    ->label(__('admin.mark_as_read'))
                    ->icon('heroicon-o-check')
                    ->action(function (DatabaseNotification $record) {
                        $record->markAsRead();
                    })
                    ->visible(fn (DatabaseNotification $record) => is_null($record->read_at)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_read')
                        ->label(__('admin.mark_as_read'))
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->markAsRead();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $admin = auth()->guard('admin')->user();
        
        if ($admin->super_admin == 1) {
            // Super admin can see all notifications
            return DatabaseNotification::query()
                ->where('notifiable_type', Admin::class);
        }

        // Branch admin can only see their own notifications
        return DatabaseNotification::query()
            ->where('notifiable_type', Admin::class)
            ->where('notifiable_id', $admin->id);
    }
}

