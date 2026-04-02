<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\OrderResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
               
                // ─── Order details ───
                Section::make(__('admin.order_section_order_details'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('order_num')
                                    ->label(__('strings.order_id'))
                                    ->default('—'),
                                TextEntry::make('invoice_id')
                                    ->label(__('strings.invoice_id')),
                                TextEntry::make('status')
                                    ->label(__('strings.status'))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
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
                                    ->formatStateUsing(fn (string $state): string => __('strings.' . $state)),
                                TextEntry::make('type')
                                    ->label(__('strings.type')),
                                TextEntry::make('pay_with')
                                    ->label(__('strings.pay_with'))
                                    ->formatStateUsing(fn (?string $state): string => $state ? __('strings.' . $state) : '—'),
                                TextEntry::make('created_at')
                                    ->label(__('strings.created_at'))
                                    ->dateTime(),
                                TextEntry::make('schedual_date')
                                    ->label(__('admin.schedual_date'))
                                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('M j, Y H:i') : '—'),
                                TextEntry::make('sub_total')
                                    ->label(__('strings.sub_total'))
                                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? \Illuminate\Support\Number::currency($state, 'TRY') : '—'),
                                TextEntry::make('discount')
                                    ->label(__('strings.discount'))
                                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? \Illuminate\Support\Number::currency($state, 'TRY') : '—'),
                                TextEntry::make('tax')
                                    ->label(__('strings.tax'))
                                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? \Illuminate\Support\Number::currency($state, 'TRY') : '—'),
                                TextEntry::make('delivery_charge')
                                    ->label(__('strings.delivery_charge'))
                                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? \Illuminate\Support\Number::currency($state, 'TRY') : '—'),
                                TextEntry::make('total_price')
                                    ->label(__('strings.total_price'))
                                    ->money('try'),
                                TextEntry::make('coupon.code')
                                    ->label(__('strings.coupon_code'))
                                    ->default('—'),
                                TextEntry::make('cancelled_reason')
                                    ->label(__('strings.cancelled_reason'))
                                    ->visible(fn ($record) => $record->status === 'canceled')
                                    ->default('—'),
                            ]),
                    ])
                     ->columnSpan(3),
 
         Grid::make(2)
    ->schema([
        // Column 1: User Details
        Section::make(__('admin.order_section_user_details'))
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextEntry::make('user.first_name')
                            ->label(__('strings.first_name')),
                        TextEntry::make('user.last_name')
                            ->label(__('strings.last_name')),
                        TextEntry::make('user.phone_number')
                            ->label(__('strings.phone_number')),
                        TextEntry::make('user.total_points')
                            ->label(__('strings.total_points'))
                            ->default('—'),
                        TextEntry::make('user.total_stars')
                            ->label(__('strings.total_stars'))
                            ->default('—'),
                        TextEntry::make('user_rank')
                            ->label(__('admin.ranks'))
                            ->getStateUsing(fn($record) => $record->user?->rank()?->name ?? '—'),
                    ]),
            ])
            ->columnSpan(1),

        // Column 2: Payment
        Section::make(__('admin.order_section_payment'))
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextEntry::make('userPayment.name')
                            ->label(__('strings.payment_method'))
                            ->default('—'),
                        TextEntry::make('userPayment.card_number')
                            ->label(__('strings.card_number'))
                            ->formatStateUsing(fn(?string $state): string => $state ? '•••• ' . substr($state, -4) : '—')
                            ->default('—'),
                        TextEntry::make('userPayment.expire_date')
                            ->label(__('strings.expire_date'))
                            ->default('—'),
                    ]),
            ])
            ->columnSpan(1)
            ->visible(fn($record) => $record->userPayment !== null && $record->user_payment_id !== null),
    ]),
                // ─── Branch & delivery address ───
                Section::make(__('admin.order_section_branch_delivery'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label(__('strings.branch_name'))
                                    ->visible(fn () => auth()->guard('admin')->user()?->super_admin == 1),
                                TextEntry::make('userLocation.address_title')
                                    ->label(__('strings.address_title'))
                                    ->default('—'),
                                TextEntry::make('userLocation.name_address')
                                    ->label(__('strings.name_address'))
                                    ->default('—'),
                                TextEntry::make('userLocation.building_number')
                                    ->label(__('strings.building_number'))
                                    ->default('—'),
                                TextEntry::make('userLocation.floor')
                                    ->label(__('strings.floor'))
                                    ->default('—'),
                                TextEntry::make('userLocation.apartment')
                                    ->label(__('strings.apartment'))
                                    ->default('—'),
                                TextEntry::make('userLocation.address_description')
                                    ->label(__('strings.address_description'))
                                    ->columnSpanFull()
                                    ->default('—'),
                                TextEntry::make('userLocation.first_name')
                                    ->label(__('strings.first_name'))
                                    ->default('—'),
                                TextEntry::make('userLocation.last_name')
                                    ->label(__('strings.last_name'))
                                    ->default('—'),
                                TextEntry::make('userLocation.phone_number')
                                    ->label(__('strings.phone_number'))
                                    ->default('—'),
                            ]),
                    ])
                    ->columns(1)
                    ->visible(fn ($record) => $record->userLocation !== null),

              
            ]);
    }
}
