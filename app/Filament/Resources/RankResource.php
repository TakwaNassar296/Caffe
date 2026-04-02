<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RankResource\Pages;
use App\Models\Rank;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RankResource extends Resource
{
    protected static ?string $model = Rank::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function getNavigationLabel(): string
    {
        return __('admin.ranks');
    }

    public static function getModelLabel(): string
    {
        return __('admin.rank');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.ranks');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.settings');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.rank_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('admin.name')),

                        TextInput::make('title')
                            ->label(__('admin.title'))
                            ->maxLength(255)
                            ->placeholder(__('admin.title')),

                        FileUpload::make('image')
                            ->label(__('admin.image'))
                            ->image()
                            ->directory('uploads/ranks')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label(__('admin.description'))
                            ->rows(3)
                            ->placeholder(__('admin.description'))
                            ->columnSpanFull(),

                        TextInput::make('min_stars')
                            ->label(__('admin.min_stars'))
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->placeholder(__('admin.min_stars')),

                        TextInput::make('max_stars')
                            ->label(__('admin.max_stars'))
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->placeholder(__('admin.max_stars')),

                        TextInput::make('points_increment')
                            ->label(__('admin.points_increment'))
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->placeholder(__('admin.points_increment')),

                        TextInput::make('stars_increment')
                            ->label(__('admin.stars_increment'))
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->placeholder(__('admin.stars_increment')),

                        // ColorPicker::make('badge_color')
                        //     ->label(__('admin.badge_color'))
                        //     ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.image'))
                    ->circular(),

                Tables\Columns\TextColumn::make('min_stars')
                    ->label(__('admin.min_stars'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_stars')
                    ->label(__('admin.max_stars'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('points_increment')
                    ->label(__('admin.points_increment'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stars_increment')
                    ->label(__('admin.stars_increment'))
                    ->numeric()
                    ->sortable(),

                // Tables\Columns\ColorColumn::make('badge_color')
                //     ->label(__('admin.badge_color')),

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
            'index' => Pages\ListRanks::route('/'),
            'create' => Pages\CreateRank::route('/create'),
            'view' => Pages\ViewRank::route('/{record}'),
            'edit' => Pages\EditRank::route('/{record}/edit'),
        ];
    }
}

