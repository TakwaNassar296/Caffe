<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages; 
use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;



class EmployeeResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; 

    public static function getNavigationLabel(): string
    {
        return __('admin.employee');
    }

    public static function getModelLabel(): string
    {
        return __('admin.employee');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.employee');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.section_employees_users');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('employee');
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

                    // Automatically set the role to employee
                    Forms\Components\Hidden::make('role')
                        ->default('employee'),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->label(__('strings.password'))
                        ->revealable()
                        ->visibleOn('create')
                        ->required(fn($livewire) => $livewire instanceof Pages\CreateEmployee)
                        ->minLength(6)
                        ->maxLength(255),

                    Forms\Components\Select::make('branch_id')
                        ->relationship('assignedBranch', 'name')
                        ->label(__('admin.branch'))
                        ->required()
                        ->searchable()
                        ->preload(),    

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

                Tables\Columns\TextColumn::make('assignedBranch.name')
                    ->label(__('admin.branch'))
                    ->badge()
                    ->color('success')
                    ->sortable(),
    
   
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}