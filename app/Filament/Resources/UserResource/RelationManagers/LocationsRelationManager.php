<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Nette\Utils\Html;

class LocationsRelationManager extends RelationManager
{

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('strings.user-locations'); 
    }


    protected static string $relationship = 'locations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(2)
                    ->schema([
                        \Filament\Infolists\Components\Section::make(__('strings.location'))
                            ->columns(3)
                            ->schema([
                                TextEntry::make('name_address')->label(__('strings.name_address'))->columnSpanFull(),
                                TextEntry::make('building_number')->label(__('strings.building_number')),
                                TextEntry::make('floor')->label(__('strings.floor')),
                                TextEntry::make('apartment')->label(__('strings.apartment')),
                                TextEntry::make('address_description')->label(__('strings.address_description'))->columnSpanFull(),
                                TextEntry::make('address_title')->label(__('strings.address_title')),
                               TextEntry::make('location_map')
                                ->label(__('الموقع'))
                                ->state(__('اضغط هنا'))
                                ->url(fn ($record) => "https://www.google.com/maps?q={$record->latitude},{$record->longitude}")
                                ->openUrlInNewTab()
                                ->color('primary')
                                ->icon('heroicon-o-map'),

                            ]),
                    ]),
            ]);
    }
  public function table(Table $table): Table
{
    return $table
         ->columns([
            Tables\Columns\TextColumn::make('name_address')->label(__('strings.name_address')),
            Tables\Columns\TextColumn::make('phone_number')->label(__('strings.phone_number')),
            Tables\Columns\TextColumn::make('address_title')->label(__('strings.address_title')),
            Tables\Columns\TextColumn::make('building_number')->label(__('strings.building_number')),
            Tables\Columns\TextColumn::make('address_description')->label(__('strings.address_description'))->limit(50),
            Tables\Columns\TextColumn::make('floor')->label(__('strings.floor')),
            Tables\Columns\TextColumn::make('apartment')->label(__('strings.apartment')),
        ])
        ->filters([
            //
        ])
     
        ->actions([
            Tables\Actions\ViewAction::make(__('strings.show'))->button()->authorize(true),
         ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()->label(__('strings.delete_selected')),
            ]),
        ]);
}

}
