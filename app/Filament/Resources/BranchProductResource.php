<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchProductResource\Pages;
use App\Filament\Resources\BranchProductResource\RelationManagers;
use App\Models\BranchProduct;
use App\Models\ProductsMaterial;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class BranchProductResource extends Resource
{
    protected static ?string $model = BranchProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('admin.branch_products');
    }

    public static function getModelLabel(): string
    {
        return __('admin.branch_product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.branch_products');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.products_category');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.branch_information'))
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label(__('admin.branch'))
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('product_id')
                            ->label(__('admin.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->live(),
                           

                        Forms\Components\TextInput::make('amount')
                            ->label(__('admin.amount'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->reactive()
                            ->live()
                            ->helperText(__('admin.product_quantity_in_branch')),

                    ])
                    ->columns(3),

                Section::make(__('admin.product_materails'))
                    ->schema([
                        Placeholder::make('materials_display')
                            ->label('')
                            ->content(function (Get $get) {
                                $productId = $get('product_id');
                                $productAmount = (float) ($get('amount') ?? 0);
                                
                                if (!$productId) {
                                    return  __('admin.select_product_to_view_materials') ;
                                }

                                // Get all product materials
                                $productMaterials = ProductsMaterial::where('product_id', $productId)
                                    ->with('items.material', 'productOption')
                                    ->get();

                                if ($productMaterials->isEmpty()) {
                                    return  __('admin.no_materials_configured') ;
                                }

                                // Combine materials by material_id and unit
                                $combinedMaterials = [];
                                
                                foreach ($productMaterials as $pm) {
                                    foreach ($pm->items as $item) {
                                        if (!$item->material_id) {
                                            continue;
                                        }
                                        
                                        $key = $item->material_id . '_' . $item->unit;
                                        
                                        if (!isset($combinedMaterials[$key])) {
                                            $combinedMaterials[$key] = [
                                                'material_id' => $item->material_id,
                                                'material_name' => $item->material?->name ?? '-',
                                                'unit' => strtoupper($item->unit ?? ''),
                                                'total_quantity_per_unit' => 0,
                                            ];
                                        }
                                        
                                        // Sum quantities for same material and unit
                                        $combinedMaterials[$key]['total_quantity_per_unit'] += (float) ($item->quantity_used ?? 0);
                                    }
                                }

                                if (empty($combinedMaterials)) {
                                    return  __('admin.no_materials_configured') ;
                                }

                                // Display combined materials in a single table
                                $html = '<div class="border rounded-lg p-4 bg-gray-50">';
                                $html .= '<h4 class="font-semibold text-lg mb-3 text-gray-800">' . __('admin.product_materails') . '</h4>';
                                
                                $html .= '<div class="overflow-x-auto">';
                                $html .= '<table class="min-w-full divide-y divide-gray-200">';
                                $html .= '<thead class="bg-gray-100">';
                                $html .= '<tr>';
                                $html .= '<th class="px-4 py-2 text-right text-sm font-medium text-gray-700">' . __('admin.material') . '</th>';
                                $html .= '<th class="px-4 py-2 text-right text-sm font-medium text-gray-700">' . __('admin.quantity_per_unit') . '</th>';
                                $html .= '<th class="px-4 py-2 text-right text-sm font-medium text-gray-700">' . __('admin.unit') . '</th>';
                                $html .= '<th class="px-4 py-2 text-right text-sm font-medium text-gray-700">' . __('admin.total_needed') . '</th>';
                                $html .= '</tr>';
                                $html .= '</thead>';
                                $html .= '<tbody class="bg-white divide-y divide-gray-200">';
                                
                                foreach ($combinedMaterials as $combined) {
                                    $totalNeeded = $combined['total_quantity_per_unit'] * $productAmount;
                                    
                                    $html .= '<tr class="hover:bg-gray-50">';
                                    $html .= '<td class="px-4 py-2 text-sm text-gray-900 font-medium">' . htmlspecialchars($combined['material_name']) . '</td>';
                                    $html .= '<td class="px-4 py-2 text-sm text-gray-600">' . number_format($combined['total_quantity_per_unit'], 2) . '</td>';
                                    $html .= '<td class="px-4 py-2 text-sm"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' . htmlspecialchars($combined['unit']) . '</span></td>';
                                    $html .= '<td class="px-4 py-2 text-sm"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 font-semibold">' . number_format($totalNeeded, 2) . ' ' . htmlspecialchars($combined['unit']) . '</span></td>';
                                    $html .= '</tr>';
                                }
                                
                                $html .= '</tbody>';
                                $html .= '</table>';
                                $html .= '</div>';
                                $html .= '</div>';
                                
                                return new HtmlString($html);
                            })
                            ->visible(fn (Get $get) => !empty($get('product_id')))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get) => !empty($get('product_id'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('admin.branch'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.product'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('product.category.name')
                    ->label(__('admin.category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('status')
                    ->label(__('admin.status'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('admin.amount'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('product.price')
                    ->label(__('admin.price'))
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('admin.branch')),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        1 => __('admin.active'),
                        0 => __('admin.inactive'),
                    ])
                    ->label(__('admin.status')),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label(__('admin.out_of_stock'))
                    ->query(fn (Builder $query) => $query->where('amount', '<=', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('admin.no_branch_products'))
            ->emptyStateDescription(__('admin.no_branch_products_description'))
            ->emptyStateIcon('heroicon-o-shopping-bag');
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
            'index' => Pages\ListBranchProducts::route('/'),
            'create' => Pages\CreateBranchProduct::route('/create'),
            'view' => Pages\ViewBranchProduct::route('/{record}'),
            'edit' => Pages\EditBranchProduct::route('/{record}/edit'),
        ];
    }
}
