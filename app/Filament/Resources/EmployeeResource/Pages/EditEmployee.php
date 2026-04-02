<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),


            Action::make('changePassword')
            ->label(__('strings.change_password'))
            ->icon('heroicon-o-key')
            ->color('primary')
            ->form([
                TextInput::make('new_password')
                    ->label(__('strings.new_password'))
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->minLength(6),
            ])
            ->action(function (array $data, $record) {
                $record->password = Hash::make($data['new_password']);
                $record->save();

                Notification::make()
                    ->title(__('strings.password_updated_successfully'))
                    ->success()
                    ->send();
            }),
        ];
    }
}
