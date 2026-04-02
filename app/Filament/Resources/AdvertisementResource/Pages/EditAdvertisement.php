<?php

namespace App\Filament\Resources\AdvertisementResource\Pages;

use App\Filament\Resources\AdvertisementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisement extends EditRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

   public function mutateFormDataBeforeSave(array $data): array
{
    $record = $this->record;

    if (($data['type'] ?? null) === 'title') {

        if ($record && $record->image) {
            \Storage::disk('public')->delete($record->image);
        }

        $data['image'] = null;
    }

    if (($data['type'] ?? null) === 'image') {
        $data['title'] = null;
    }

    return $data;
}
}
