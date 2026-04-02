<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Coupon;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
 use Filament\Infolists\Components\Grid;
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationLabel(): string
    {
        return __('admin.products');
    }

    public static function getModelLabel(): string
    {
        return __('admin.products');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.products');
    }
    public static function getNavigationGroup(): string
    {
        return __('admin.products_category');
    }

    public static function getNavigationBadge(): ?string
    {
        $auth = auth()->guard('admin')->user();

        if ($auth->super_admin == 1) {
            return static::getModel()::with(['options.values'])->count();
        }

        return static::getModel()::whereHas('branches', function ($query) use ($auth) {
            $query->where('branch_id', $auth->branch->id);
        })
            ->with(['options.values'])
            ->count();
    }




    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.product'))
                    ->schema([

                        FileUpload::make('image')
                            ->multiple()
                            ->required()
                            ->panelLayout('grid')
                            ->label(__('admin.image'))
                            ->directory('uploads/products')
                            ->image(),

                        TextInput::make('name')
                            ->label(__('admin.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('points')
                        ->required()
                            ->label(__('admin.points'))
                            ->numeric()
                            ->default(null),

                        TextInput::make('price_with_points')
                            ->required()
                            ->label(__('admin.price_points'))
                            ->numeric(),

                        TextInput::make('stars')
                        ->required()
                            ->label(__('admin.stars'))
                            ->numeric()
                            ->default(null),

                        Forms\Components\Placeholder::make('total_sales')
                            ->label(__('admin.total_sales'))
                            ->content(fn ($record) => $record ? (int) $record->total_sales : '0')
                            ->visibleOn('edit'),

                        TextInput::make('title')
                        ->required()
                            ->label(__('admin.title'))
                            ->maxLength(255)
                            ->default(null),

                        Textarea::make('description')
                        ->required()
                            ->label(__('admin.description'))
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->numeric()
                            ->prefix('TRY')
                            ->required()
                            ->minValue(1)
                            ->label(__('admin.price'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                self::updatePriceAfterDiscount($get, $set);
                            }),

                        Forms\Components\Toggle::make('is_offer')
                            ->reactive()
                            ->label(__('strings.is_offer')),

                        Toggle::make('is_offer_percentage')
                            ->label(__('strings.is_offer_percentage'))
                            ->hidden(fn(Get $get) => !$get('is_offer'))
                            ->live(onBlur: true)

                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                self::updatePriceAfterDiscount($get, $set);
                            }),

                        TextInput::make('discount_rate')
                            ->numeric()
                            ->minValue(1)
                            ->prefix(function (Get $get) {
                                return $get('is_offer_percentage') ? '%' : '$';
                            })
                            ->hidden(fn(Get $get) => !$get('is_offer'))
                            ->label(__('strings.discount_rate'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                self::updatePriceAfterDiscount($get, $set);
                            }),

                        TextInput::make('price_after_discount')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(1)
                            ->hidden(fn(Get $get) => !$get('is_offer'))
                            ->label(__('strings.price_after_discount'))
                            ->disabled()
                            ->dehydrated()
                            ->required(fn(Get $get) => $get('is_offer')),
 
                        Forms\Components\Select::make('category_id')
                            ->required()
                            ->relationship(name: 'category', titleAttribute: 'name')
                            ->label(__('admin.category')),

 

                        TextInput::make('amount')
                            ->label(__('admin.amount'))
                            ->required()
                            ->numeric()
                            ->default(1),

                        Forms\Components\Placeholder::make('remaining_quantity')
                            ->label(__('admin.remaining_quantity'))
                            ->content(fn ($record) => $record ? $record->remaining_quantity : '-')
                            ->visibleOn('edit'),

                        TextInput::make('stat_minutes')
                             ->label(__('admin.stat_minutes'))
                             ->required()
                             ->numeric()
                             ->default(0),


                            TextInput::make('end_minutes')
                            ->label(__('admin.end_minutes'))
                             ->required()
                            ->numeric()
                            ->default(0),

                          

                        Forms\Components\Select::make('relatedProducts')
                            ->label(__('strings.related'))
                            ->multiple()
                            ->relationship(
                                name: 'relatedProducts',
                                titleAttribute: 'name'
                            )
                             ->searchable()
                            ->preload() ,

                    ])->columns(3),

                Section::make(__('admin.options_section'))->schema([
                    Repeater::make('options')
                        ->label(__('admin.options'))
                        ->relationship('options')
                        ->schema([
                            TextInput::make('name')
                                ->label(__('admin.option_name'))
                                ->required()->columns(2),

                            Repeater::make('values')
                                ->relationship('values')
                                ->schema([
                                    TextInput::make('value')
                                        ->label(__('admin.value'))
                                        ->required()->columns(2),
                                    TextInput::make('extra_price')
                                        ->label(__('admin.price'))
                                        ->required()->columns(2)
                                        ->numeric()
                                        ->default(0),

                                    Checkbox::make('is_recommended')
                                        ->inline()
                                        ->label(__('admin.is_recommended'))
                                        ->default(false),
                                ])->addable(false)
                                ->columns(2)
                                ->label(__('admin.value')),
                        ])
                        ->label(__('admin.option_name'))
                        ->columns(1),
                ]),

                Toggle::make('is_active')
                    ->label(__('admin.is_active'))
                    ->required()
                    ->default(true),

                Toggle::make('appere_in_cart')
                    ->label(__('strings.appere_in_cart'))
                    ->required()
                    ->default(false),

                Tabs::make(__('admin.preparation_steps'))
                    ->tabs([
                        Tab::make(__('admin.preparation_steps'))
                            ->schema([
                                Repeater::make('perpar_steps')
                                    ->label(__('admin.preparation_steps'))
                                    ->schema([
                                        TextInput::make('step')
                                            ->label(__('admin.step'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel(__('admin.add_step'))
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['step'] ?? null),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])->columns(3);
    }

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
       Grid::make(2)
    ->schema([
        // Column 1: General Info
        InfolistSection::make(__('admin.general_information'))
            ->schema([
                     ImageEntry::make('image')
                ->label(__('strings.image'))
                ->height(30)
                ->width(40),

                TextEntry::make('name')
                    ->label(__('admin.name')),
     
           

                TextEntry::make('points')->label(__('admin.points'))->badge(),
                TextEntry::make('stars')->label(__('admin.stars'))->badge(),
                TextEntry::make('total_sales')->label(__('admin.total_sales'))->badge(),
                TextEntry::make('rating')->label(__('admin.rating'))->badge(),
                TextEntry::make('total_rating')->label(__('admin.total_rating'))->badge(),

                

                    
            ])
            ->columns(4)
            ->columnSpan(1),

        // Column 2: Pricing
        InfolistSection::make(__('admin.pricing'))
            ->schema([
                TextEntry::make('price')->label(__('admin.price')),

                TextEntry::make('discount_rate')
                    ->label(__('strings.discount_rate'))
                    ->hidden(fn($record) => !$record->is_offer),

                TextEntry::make('price_after_discount')
                    ->label(__('strings.price_after_discount'))
                    ->hidden(fn($record) => !$record->is_offer),

                TextEntry::make('amount')->label(__('admin.amount')),
                TextEntry::make('remaining_quantity')->label(__('admin.remaining_quantity')),
            ])
            ->columns(3)
            ->columnSpan(1),
    ]),

      InfolistSection::make(__('admin.description'))
            ->schema([
   TextEntry::make('title')
                    ->label(__('admin.title')),
               TextEntry::make('description')
                    ->label(__('admin.description')),
        
   ]),

          Grid::make(2)
    ->schema([
        // Column 1: Preparation Steps
        InfolistSection::make(__('admin.preparation_steps'))
            ->schema([
                RepeatableEntry::make('perpar_steps')
                    ->label(__('admin.preparation_steps'))
                    ->schema([
                        TextEntry::make('step')
                            ->label(__('admin.step')),
                    ])
                    ->columns(2),
            ])
            ->columnSpan(1),

        // Column 2: Product Options
        InfolistSection::make(__('admin.product_options'))
            ->schema([
                RepeatableEntry::make('options')
                    ->label(__('admin.product_options'))
                    ->schema([
                        TextEntry::make('name')->label(__('admin.option_name')),

                        RepeatableEntry::make('values')
                            ->label(__('admin.option_values'))
                            ->schema([
                                TextEntry::make('value')->label(__('admin.value')),
                                TextEntry::make('extra_price')->label(__('admin.price')),
                            ])->columns(3),
                    ]),
            ])
            ->columnSpan(1),
    ]),
    ]);
}
 

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.image'))->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label(__('admin.name')),
 

                Tables\Columns\TextColumn::make('total_sales')
                    ->numeric()
                    ->sortable()
                    ->label(__('admin.total_sales')),
 

                Tables\Columns\TextColumn::make('price')
                    ->money('TRY')
                    ->sortable()
                    ->label(__('admin.price')),

                Tables\Columns\TextColumn::make('coupon.value')
                    ->numeric()
                    ->visible(fn($record) => $record?->coupon !== null)
                    ->label(__('admin.coupon_value')),
 

                  


                Tables\Columns\TextColumn::make('remaining_quantity')
                    ->numeric()
                    ->sortable()
                    ->label(__('admin.remaining_quantity')),

             


                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(__('admin.is_active')),


                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('admin.created_at')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('admin.updated_at')),
            ])

          
            ->actions([
                Tables\Actions\ViewAction::make()->button(),
                Tables\Actions\EditAction::make()->button()->visible(fn() => auth()->guard('admin')->user()->super_admin == 1),
                
                Tables\Actions\ReplicateAction::make()
                    ->label('نسخ المنتج')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->visible(fn() => auth()->guard('admin')->user()->super_admin == 1),
            ]);
    }

    public static function getRelations(): array
    {
        return [
         ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    private static function updatePriceAfterDiscount(Get $get, Set $set): void
    {
        $price = (float)($get('price') ?? 1);
        $discount = (float)($get('discount_rate') ?? 1);
        $isPercentage = (bool)$get('is_offer_percentage');

        if ($isPercentage) {
            $calculated = $price - ($price * ($discount / 100));
        } else {
            $calculated = $price - $discount;
        }

        $set('price_after_discount', number_format(max($calculated, 0), 2));
    }

  

}
