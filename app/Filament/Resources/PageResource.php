<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationLabel(): string
    {
        return __('admin.pages');
    }

    public static function getModelLabel(): string
    {
        return __('admin.page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.pages');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_content');
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
                    ->schema([
                        TextInput::make('title')
                            ->label(__('admin.title'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('admin.title')),

                        Textarea::make('content')
                            ->label(__('admin.content'))
                            ->rows(5)
                            ->required()
                            ->placeholder(__('admin.content'))
                            ->columnSpanFull(),

                        TextInput::make('phone_number')
                            ->label(__('admin.phone_number'))
                            ->tel()
                            ->maxLength(255)
                            ->placeholder(__('admin.phone_number'))
                            ->visible(fn ($record) => $record && $record->slug === 'support'),

                        FileUpload::make('image')
                            ->label(__('admin.image'))
                            ->image()
                            ->directory('uploads/pages')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !$record || !$record->slug || $record->slug !== 'support'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.slug'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state === 'support' ? __('admin.support') : __('admin.terms_and_conditions')),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('admin.phone_number'))
                    ->searchable()
                    ->visible(fn ($record) => $record && $record->slug === 'support'),

                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.image'))
                    ->circular()
                    ->visible(fn ($record) => !$record || !$record->slug || $record->slug !== 'support'),

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
            'index' => Pages\ListPages::route('/'),
            'view' => Pages\ViewPage::route('/{record}'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}

