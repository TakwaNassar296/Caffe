<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchManagerResource\Pages;
use App\Models\Admin; // Use Admin model instead of BranchManager
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchManagerResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function getNavigationLabel(): string
    {
        return __('admin.branch_managers');
    }

    public static function getModelLabel(): string
    {
        return __('admin.branch_manager');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.branch_managers');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_employees_users');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('branch_manger');
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

                    Forms\Components\Select::make('branch_id')
                        ->relationship('assignedBranch', 'name')
                        ->label(__('admin.branch'))
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\Hidden::make('role')
                        ->default('branch_manger'),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->label(__('strings.password'))
                        ->revealable()
                        ->visibleOn('create')
                        ->required()
                        ->minLength(6),
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
                    ->label(__('strings.email'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('assignedBranch.name')
                    ->label(__('admin.branch'))
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranchManagers::route('/'),
            'create' => Pages\CreateBranchManager::route('/create'),
            'view' => Pages\ViewBranchManager::route('/{record}'),
            'edit' => Pages\EditBranchManager::route('/{record}/edit'),
        ];
    }
}