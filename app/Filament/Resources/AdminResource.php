<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Admin;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AdminResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AdminResource\RelationManagers;
use Illuminate\Database\Eloquent\Model;


class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

        public static function getNavigationLabel(): string
    {
        return __('strings.super_admins');
    }

    public static function getModelLabel(): string
    {
        return __('strings.super_admins');
    }

    public static function getPluralModelLabel(): string
    {
        return __('strings.super_admins');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_employees_users');
    }

    public static function getEloquentQuery(): Builder
    {
        
        return parent::getEloquentQuery()->role('super_admin');
    }

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->label(__('strings.name'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->label(__('strings.email'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\Hidden::make('role')
                        ->default('super_admin'),

                
                        Forms\Components\TextInput::make('password')
                        ->password()
                        ->label(__('strings.password'))
                        ->revealable()
                        ->visibleOn('create')
                        ->required(fn($livewire) => $livewire instanceof Pages\CreateAdmin)
                        ->minLength(6)
                        ->maxLength(255),


                ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label(__('strings.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->label(__('strings.email'))    
                    ->searchable(),
   
                
                Tables\Columns\TextColumn::make('total_points')
                    ->sortable()
                    ->label(__('admin.total_points'))
                    ->numeric(
                        decimalPlaces: 2,
                    )
                    ->default(0)
                    ->toggleable(),
            
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
               // Tables\Actions\DeleteAction::make()->hidden(fn($livewire, $record) => $record->id == 1),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                   
                ])  ,
            ])->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->role != 'super_admin',
            );
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'view' => Pages\ViewAdmin::route('/{record}'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }

     
}