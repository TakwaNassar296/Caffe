<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Coupon;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CouponResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\CouponResource\RelationManagers;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return __('admin.coupons');
    }

    public static function getModelLabel(): string
    {
        return __('admin.coupons');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('admin.coupons');
    }
    public static function getNavigationGroup(): string
    {
        return __('admin.finance');
    }

        public static function getNavigationBadge(): ?string
    {
            return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Section::make()
               ->columns(2)
                ->columnSpanFull()
                ->schema([

                    Forms\Components\TextInput::make('name')
                    ->label(__('strings.name'))
                    ->required()
                    ->maxLength(255),
                 
                  TextInput::make('code')
                  ->label(__('strings.coupon_code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30),


                    Select::make('kind')
                    ->label(__('strings.kind'))
                    ->options([
                        'all' => __('strings.all'),
                        'special' => __('strings.select_special_branch'),
                    ])
                    ->reactive()
                    ->required(),

                    Select::make('branch_id')
                    ->label(__('strings.branch'))
                    ->relationship('branchs', 'name')
                    ->visible(fn ($get) => $get('kind') === 'special')
                    ->nullable()
                    ->columnSpan(2),

                   TextInput::make('used')
                   ->label(__('strings.used'))
                   ->visible((fn($livewire) => $livewire instanceof EditCoupon))
                   ->disabled(),

                //   Select::make('type')
                //     ->label(__('strings.type'))
                //     ->live()
                //     ->reactive()
                //     ->options([
                //         'fixed' => __('strings.fixed'),
                //         'percent' => __('strings.percent'),
                //     ])
                //     ->required(),

                Forms\Components\Hidden::make('type')
    ->default('fixed')
    ->required(),
    
                  TextInput::make('value')
                    ->suffix(fn($get) => $get('type') === 'fixed' ?  '$' : '%')
                    ->label(__('strings.coupon_value')) 
                    ->required()
                    ->numeric(),
                  DatePicker::make('start_date')
                  ->label(__('strings.start_date'))
                    ->required(),
                  DatePicker::make('end_date')
                    ->label(__('strings.end_date'))
                    ->required(),
                  TextInput::make('usage_limit')
                    ->label(__('strings.usage_limit'))
                    ->numeric()
                    ->default(null),
                  
                  Toggle::make('is_active')
                  ->label(__('strings.is_active'))
                  ->default(true)
                    ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('used')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view' => Pages\ViewCoupon::route('/{record}'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
    
    
}
