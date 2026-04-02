<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers\BranchMaterialRelationManager;
use App\Filament\Resources\BranchResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\BranchResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\BranchResource\RelationManagers\ShipmentsRelationManager;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\Material;
use App\Models\Product;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationLabel(): string
    {
        return __('strings.branches');
    }

    public static function getModelLabel(): string
    {
        return __('strings.branches');
    }

    public static function getPluralModelLabel(): string
    {
        return __('strings.branches');
    }
    public static function getNavigationGroup(): string
    {
        return __('admin.section_branches_cities');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(8)
                    ->schema([
                        Group::make()
                            ->schema([
                                Forms\Components\Section::make(__('strings.branch_details'))
                                    ->schema([

                                        Forms\Components\Select::make('admin_id')
                                            ->label(__('strings.manager_responsible_for_the_branch'))
                                            ->required()
                                            ->options(Admin::where('id' , '!=', 1)->get()->pluck('name', 'id')),

                                        Forms\Components\TextInput::make('name')
                                            ->label(__('strings.branch_name'))
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('description')
                                            ->label(__('strings.branch_description'))
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\Select::make('coupons_id')
                                            ->label(__('strings.coupon_option'))
                                            ->nullable()
                                            ->relationship('coupons', 'name'),

                                        Forms\Components\Select::make('governorate_id')
                                            ->label(__('strings.governorate'))
                                            ->required()
                                            ->relationship('governorate', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(fn(Set $set) => $set('city_id', null)),

                                        Forms\Components\Select::make('city_id')
                                            ->label(__('strings.city'))
                                            ->required()
                                            ->relationship('city', 'name', modifyQueryUsing: fn($query, Get $get) => $query->where('governorate_id', $get('governorate_id')))
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\TextInput::make('code')
                                            ->label("ID")
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),


                                        Forms\Components\TimePicker::make('opening_date')
                                            ->label(__('strings.opening_date'))
                                            ->required(),

                                        Forms\Components\TimePicker::make('close_date')
                                            ->label(__('strings.close_date'))
                                            // ->after('opening_date')
                                            ->required(),

                                        Forms\Components\TextInput::make('scope_work')
                                            ->label(__('strings.scope_work'))
                                            ->prefix('KM')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxLength(255),

                                        Forms\Components\Hidden::make('latitude')
                                            ->required(),
                                        Forms\Components\Hidden::make('longitude')
                                            ->required(),

                                        Forms\Components\TextInput::make('phone_number')
                                            ->label(__('strings.phone_number'))
                                            ->tel()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Grid::make(1)
                                            ->schema([
                                                // Forms\Components\Checkbox::make('select_all_products')
                                                //     ->label('اختيار كل المنتجات')
                                                //     ->reactive()
                                                //     ->afterStateUpdated(function (Set $set, $state) {
                                                //         if ($state) {
                                                //             $allProducts = Product::pluck('id')->toArray();
                                                //             $set('select_custom_products', false);
                                                //             $set('products', $allProducts);
                                                //             $set('all_products_ids', $allProducts);
                                                //         } else {
                                                //             $set('products', []);
                                                //             $set('all_products_ids', null);
                                                //         }
                                                //     }),

                                                // Forms\Components\Checkbox::make('select_custom_products')
                                                //     ->label('اختيار منتجات مخصصة')
                                                //     ->reactive()
                                                //     ->afterStateUpdated(function (Set $set, $state) {
                                                //         if ($state) {
                                                //             $set('select_all_products', false);
                                                //             $set('products', []);
                                                //             $set('all_products_ids', null);
                                                //         }
                                                //     }),

                                                Forms\Components\Select::make('products')
                                                    ->label('أختيار منتجات')
                                                    ->multiple()
                                                    ->relationship('products', 'name')
                                                    ->searchable()
                                                    ->hidden(fn(callable $get) => ! $get('select_custom_products'))
                                                    ->preload()
                                                    ->required(fn(callable $get) => $get('select_custom_products'))
                                                    ->dehydrated(fn(callable $get) => ! $get('select_all_products')),

                                                Forms\Components\Hidden::make('all_products_ids')
                                                    ->default([])
                                                    ->dehydrated(fn(callable $get) => $get('select_all_products')),
                                            ])->columns(1)

                                    ])->columns(2),

                            ])
                            ->columnSpan(5),

                        Forms\Components\Section::make(__('strings.location'))
                            ->schema([
                                Map::make('location')
                                    ->required()
                                    ->label(__('strings.location'))
                                    ->defaultLocation(latitude: 40.4168, longitude: -3.7038)
                                    ->draggable(true)
                                    ->clickable(true)
                                    ->showMarker()
                                    ->markerColor('#DC2626')
                                    ->showFullscreenControl()
                                    // ->geolocate(true)
                                    // ->geolocateLabel('تحديد موقعي الحالي')
                                    // ->geolocateOnLoad(false)
                                    ->zoom(15)
                                    ->minZoom(0)
                                    ->maxZoom(28)
                                    ->liveLocation(true, true, 5000)
                                    // ->columnSpanFull()
                                    ->afterStateUpdated(function (Set $set, ?array $state): void {
                                        $set('latitude', $state['lat']);
                                        $set('longitude', $state['lng']);
                                    })
                                    ->afterStateHydrated(function ($state, $record, Set $set): void {
                                        $set('location', [
                                            'lat' => $record?->latitude,
                                            'lng' => $record?->longitude,
                                        ]);
                                    }),

                                Forms\Components\Section::make(__('strings.branch_image'))
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->label(__('strings.branch_image'))
                                            ->image()
                                            ->required(),

                                        Forms\Components\FileUpload::make('images')
                                            ->label(__('strings.images'))
                                            ->image()->multiple()
                                            ->required(),

                                    ])

                            ])->columnSpan(3),

                    ]),


                // Repeater::make('branchMaterial')
                //     ->relationship('branchMaterial')
                //     ->label(__('admin.add_required_raw_materials_for_branch'))
                //     ->schema([

                //         // Material selection
                //         Forms\Components\Select::make('material_id')
                //             ->label(__('admin.select_material'))
                //             ->required()
                //             ->options(function (Get $get) {
                //                 return Material::pluck('name', 'id')->toArray();
                //             })
                //             ->disableOptionWhen(function ($value, Get $get) {
                //                 $selectedMaterials = collect($get('../../branchMaterial') ?? [])
                //                     ->pluck('material_id')
                //                     ->filter()
                //                     ->toArray();

                //                 $current = $get('material_id');
                //                 return in_array($value, $selectedMaterials) && $value !== $current;
                //             })
                //             ->reactive()
                //             ->afterStateUpdated(function ($state, Get $get) {
                //                 $allMaterials = collect($get('../../branchMaterial') ?? [])
                //                     ->pluck('material_id')
                //                     ->filter()
                //                     ->toArray();

                //                 // If there’s a duplicate in the same form, show notification
                //                 if (count($allMaterials) !== count(array_unique($allMaterials))) {
                //                     Notification::make()
                //                         ->title(__('admin.material_already_selected_in_form'))
                //                         ->danger()
                //                         ->send();
                //                 }
                //             })
                //             ->relationship('material', 'name'),

                //         // Quantity input
                //         Forms\Components\TextInput::make('quantity_in_stock')
                //             ->label(__('admin.give_quantity'))
                //             ->numeric()
                //             ->default(0.00)
                //             ->required()
                //             ->rules(function (callable $get) {
                //                 $materialId   = $get('material_id');
                //                 $selectedUnit = $get('unit');

                //                 if ($materialId && $selectedUnit) {
                //                     $material = Material::find($materialId);
                //                     if ($material) {
                //                         return [
                //                             'min:0',
                //                             function ($attribute, $value, $fail) use ($material, $selectedUnit) {
                //                                 $error = $material->validateQuantity($value, $selectedUnit);
                //                                 if ($error) {
                //                                     $fail($error);
                //                                 }
                //                             }
                //                         ];
                //                     }
                //                 }
                //                 return ['min:0'];
                //             }),

                //         // Unit selection
                //         Forms\Components\Select::make('unit')
                //             ->label(__('admin.unit'))
                //             ->required()
                //             ->options([
                //                 'ml'  => __('admin.ml'),
                //                 'l'   => __('admin.l'),
                //                 'g'   => __('admin.g'),
                //                 'kg'  => __('admin.kg'),
                //                 'pcs' => __('admin.pcs'),
                //             ]),
                //     ])
                //     ->columns(3)
                //     ->columnSpanFull()

            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('image')
                    ->label(__('strings.branch_image'))
                    ->circular(),

                Tables\Columns\TextColumn::make('code')
                    ->label("ID")
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('strings.branch_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('strings.branch_description'))
                    ->limit(25)
                    ->searchable(),

                Tables\Columns\TextColumn::make('governorate.name')
                    ->label(__('strings.governorate'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city.name')
                    ->label(__('strings.city'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('opening_date')
                    ->label(__('strings.opening_date'))
                    // ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('close_date')
                    ->label(__('strings.close_date'))

                    ->sortable(),
                Tables\Columns\TextColumn::make('scope_work')
                    ->label(__('strings.scope_work'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->label(__('strings.latitude'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->label(__('strings.longitude'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('strings.phone_number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('name')
                    ->visible(fn() => auth()->guard('admin')->user()->super_admin == 1)
                    ->label(__(__('strings.branch_name')))
                    ->options(fn() => \App\Models\Branch::pluck('name', 'id')->toArray())
                    ->searchable(),

                SelectFilter::make('governorate_id')
                    ->label(__('strings.governorate'))
                    ->relationship('governorate', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('city_id')
                    ->label(__('strings.city'))
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_consumption')
                    ->label(__('admin.view_consumption_history'))
                    ->icon('heroicon-o-arrow-trending-down')
                    ->color('info')
                    ->url(fn ($record) => BranchResource::getUrl('view', ['record' => $record]) . '?activeRelationManager=0&activeTab=materialConsumptions')
                    ->openUrlInNewTab(false),
                ReplicateAction::make()
                    ->excludeAttributes(['code', 'phone_number'])
                    ->beforeReplicaSaved(function (Branch $replica): void {
                        // Generate unique code
                        $replica->code = 'BRANCH-' . uniqid();
                        // Generate unique phone number
                        $replica->phone_number = 'PHONE-' . uniqid();
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
            OrdersRelationManager::class,
            ProductsRelationManager::class,
            BranchMaterialRelationManager::class,
            ShipmentsRelationManager::class,
            \App\Filament\Resources\BranchResource\RelationManagers\MaterialConsumptionsRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'view' => Pages\ViewBranch::route('/{record}'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
