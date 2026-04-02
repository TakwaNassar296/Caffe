<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\UserResource;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                        ->minLength(8),
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
