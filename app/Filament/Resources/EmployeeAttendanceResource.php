<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeAttendanceResource\Pages;
use App\Models\EmployeeAttendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EmployeeAttendanceResource extends Resource
{
    protected static ?string $model = EmployeeAttendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function getNavigationLabel(): string
    {
        return __('admin.attendance_absence');
    }

    public static function getModelLabel(): string
    {
        return __('admin.attendance_record');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.attendance_absence');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.branches_managment');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label(__('admin.employee'))
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('branch_id')
                    ->label(__('admin.branch'))
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('date')
                    ->label(__('admin.date'))
                    ->required()
                    ->default(now())
                    ->displayFormat('Y-m-d')
                    ->native(false)
                    ->columnSpan(1),

                Forms\Components\TimePicker::make('attendance_time')
                    ->label(__('admin.attendance_time'))
                    ->seconds(false)
                    ->columnSpan(1),

                Forms\Components\TimePicker::make('departure_time')
                    ->label(__('admin.departure_time'))
                    ->seconds(false)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('hours_worked')
                    ->label(__('admin.hours_worked'))
                    ->numeric()
                    ->suffix(__('admin.minutes'))
                    ->helperText(__('admin.hours_worked_helper'))
                    ->columnSpan(1),

                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.notes'))
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label(__('admin.employee'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('admin.branch'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('admin.date'))
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attendance_time')
                    ->label(__('admin.attendance_time'))
                    ->formatStateUsing(function ($state) {
                        return $state ? Carbon::parse($state)->format('H:i') : '—';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('departure_time')
                    ->label(__('admin.departure_time'))
                    ->formatStateUsing(function ($state) {
                        return $state ? Carbon::parse($state)->format('H:i') : '—';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('hours_worked_formatted')
                    ->label(__('admin.hours_worked'))
                    ->formatStateUsing(fn ($record) => $record->hours_worked_formatted ?? '—')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('hours_worked', $direction);
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label(__('admin.notes'))
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('admin.branch'))
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('admin.from_date'))
                            ->native(false)
                            ->displayFormat('Y-m-d'),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('admin.to_date'))
                            ->native(false)
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('today')
                    ->label(__('admin.today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', Carbon::today())),
            ])
            ->actions([
             //   Tables\Actions\EditAction::make(),
               // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
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
            'index' => Pages\ListEmployeeAttendances::route('/'),
            //'create' => Pages\CreateEmployeeAttendance::route('/create'),
            //'edit' => Pages\EditEmployeeAttendance::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['employee', 'branch']);
    }
}
