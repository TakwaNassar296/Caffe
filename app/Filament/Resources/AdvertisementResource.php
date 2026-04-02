<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Advertisement;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\AdvertisementResource\Pages;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    public static function getNavigationLabel(): string
    {
        return __('admin.advertisements');
    }

    public static function getModelLabel(): string
    {
        return __('admin.advertisements');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.advertisements');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_content');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('type')
                    ->label(__('strings.kind'))
                    ->options([
                        'title' => __('strings.title'),
                        'image' => __('strings.image'),
                    ])
                    ->reactive(),

                TextInput::make('title')
                    ->label(__('strings.title'))
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('type') === 'title')
                    ->dehydrated(fn ($get) => $get('type') === 'title'),

                FileUpload::make('image')
                    ->label(__('strings.image'))
                    ->image()
                    ->directory('sliders')
                    ->visible(fn ($get) => $get('type') === 'image')
                    ->dehydrated(fn ($get) => $get('type') === 'image')
                    ->nullable(),

                Forms\Components\Toggle::make('status')
                    ->label(__('strings.status'))
                    ->default(true)
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('strings.title'))
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label(__('strings.image')),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAdvertisements::route('/'),
            // 'create' => Pages\CreateAdvertisement::route('/create'),
            // 'view' => Pages\ViewAdvertisement::route('/{record}'),
            // 'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }
}
