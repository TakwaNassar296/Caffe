<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\WasteMaterial;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\WasteMaterialResource\Pages;
use App\Filament\Resources\WasteMaterialResource\RelationManagers;

class WasteMaterialResource extends Resource
{
    protected static ?string $model = WasteMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';

    public static function getNavigationLabel(): string
    {
        return __('admin.waste_material');
    }

    public static function getModelLabel(): string
    {
        return __('admin.waste_material');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.waste_material');
    }

   public static function getNavigationGroup(): string
    {
        return __('admin.materials');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth('admin')->user();

        if ($user->role == 'super_admin') {
            return static::getModel()::count();
        }
        return $user->branch->wasteMaterials()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('branch_id')
                    ->visibleOn('create')
                    ->default(function ($record) {
                        $user = Auth::guard('admin')->user();
                        return $user && $user->role !== 'super_admin' ? $user->branch?->id : $record?->branch_id;
                    }),


                Forms\Components\Select::make('material_id')
                    ->label(__('strings.select_material'))
                    ->relationship('material', 'name')
                    ->live()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {

                        if ($state) {
                            $material = \App\Models\Material::find($state);
                            if ($material) {
                                $set('unit', $material->unit);
                            }
                        } else {
                            $set('unit', null);
                        }
                    }),


                Forms\Components\Select::make('unit')
                    ->label(__('admin.unit'))
                    ->required()
                    ->options(
                        fn(Get $get) => $get('unit')
                            ? [$get('unit') => __("admin." . $get('unit'))]
                            : []
                    )
                    ->native(false),

                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('strings.branch'))
                    ->sortable()
                    ->visible(fn () => auth('admin')->user()->role === 'super_admin')
                    ->searchable(),

                 Tables\Columns\TextColumn::make('material.name')
                ->label(__('strings.material'))
                ->sortable()
                ->searchable(),
              
                Tables\Columns\TextColumn::make('unit')
                ->label(__('strings.unit'))
                ->formatStateUsing(function ($state) {
                    return match ($state) {
                        'ml'  => __('admin.ml'),
                        'l'   => __('admin.l'),
                        'g'   => __('admin.g'),
                        'kg'  => __('admin.kg'),
                        'pcs' => __('admin.pcs'),
                        default => $state,
                    };
                }),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
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
                Tables\Actions\EditAction::make()->hidden(fn () => auth('admin')->user()->role === 'super_admin'),
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
            'index' => Pages\ListWasteMaterials::route('/'),
            // 'create' => Pages\CreateWasteMaterial::route('/create'),
            // 'view' => Pages\ViewWasteMaterial::route('/{record}'),
            // 'edit' => Pages\EditWasteMaterial::route('/{record}/edit'),
        ];
    }
}
