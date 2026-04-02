<?php

namespace App\Filament\Resources\DriverResource\Pages;

use Filament\Actions;
use App\Models\Branch;
use App\Models\Driver;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Illuminate\Support\Facades\Storage;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\DriverResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Action::make('download_registration')
                ->label(__('admin.download_vehicle_registration_document'))
                ->url(fn($record) => Storage::disk('public')->url($record->vehicle_registration_document))
                ->openUrlInNewTab(),


            Action::make('vehicle_insurance_document')
                ->label(__('admin.download_vehicle_insurance_document'))
                ->url(fn($record) => Storage::disk('public')->url($record->vehicle_insurance_document))
                ->openUrlInNewTab(),

            Action::make('driving_license_photo')
                ->label(__('admin.download_driving_license_photo'))
                ->url(fn($record) => Storage::disk('public')->url($record->driving_license_photo))
                ->openUrlInNewTab(),


            Action::make('change_status')
                ->label(__('admin.change_status'))
                ->form([
                    Select::make('status')
                        ->label(__('admin.status'))
                        ->options([
                            'accepted' => __('admin.accepted'),
                            'rejected' => __('admin.rejected'),
                            // 'pending' => 'Pending',
                        ])
                        ->required()
                        ->reactive(),

                    Textarea::make('reject_reason')
                        ->label(__('admin.reject_reason'))
                        ->visible(fn($get) => $get('status') === 'rejected')
                        ->required(fn($get) => $get('status') === 'rejected'),




                    Select::make('branch')
                        ->relationship('branches', 'name')
                        ->multiple()
                        ->preload()
                        ->label(__('strings.branches'))
                        ->visible(fn($get) => $get('status') === 'accepted')
                        ->required(fn($get) => $get('status') === 'accepted'),
                ])
                ->action(function (Driver $record, array $data) {
                    $record->status = $data['status'];

                    if ($data['status'] === 'rejected') {
                        $record->reject_reason = $data['reject_reason'];

                        Notification::make()
                            ->success()
                            ->title(__('strings.driver_rejected'))
                            ->send();
                    }

                    if ($data['status'] === 'accepted' && isset($data['branch'])) {
                        $record->branches()->syncWithoutDetaching($data['branch']);

                        Notification::make()
                            ->success()
                            ->title(__('strings.driver_accepted_assigned_to_branches'))
                            ->send();
                    }

                    $record->save();
                }),


        ];
    }



    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('strings.personal_information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('profile_image')
                                    ->label(__('strings.profile_image'))
                                    ->circular(),

                                TextEntry::make('first_name')->label(__('strings.first_name')),
                                TextEntry::make('last_name')->label(__('strings.last_name')),
                                TextEntry::make('phone_number')->label(__('strings.phone_number')),
                                TextEntry::make('email')->label(__('strings.email')),
                                TextEntry::make('date_of_birth')->label(__('strings.date_of_birth'))->date(),
                                TextEntry::make('nationality')->label(__('strings.nationality')),
                            ]),
                    ]),

                Section::make(__('strings.vehicle_information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('type_of_vehicle')->label(__('strings.type_of_vehicle')),
                                TextEntry::make('vehicle_model')->label(__('strings.vehicle_model')),
                                TextEntry::make('year_of_manufacture')->label(__('strings.year_of_manufacture')),
                                TextEntry::make('license_plate_number')->label(__('strings.license_plate_number')),
                                IconEntry::make('have_gps')->label(__('strings.have_gps'))->boolean(),
                            ]),
                    ]),

                Section::make(__('strings.license_information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('license_issue_date')->label(__('strings.license_issue_date'))->date(),
                                TextEntry::make('license_expiry_date')->label(__('strings.license_expiry_date'))->date(),
                                IconEntry::make('previous_experience')->label(__('strings.previous_experience'))->boolean(),
                                TextEntry::make('experience')->label(__('strings.experience'))->columnSpanFull(),
                            ]),
                    ]),

                Section::make(__('strings.additional_information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('city')->label(__('strings.city')),
                                TextEntry::make('district_area')->label(__('strings.district_area')),
                                TextEntry::make('notes')->label(__('strings.notes'))->columnSpanFull(),
                                TextEntry::make('reject_reason')->label(__('strings.reject_reason'))->columnSpanFull(),
                                TextEntry::make('status')->label(__('strings.status')),
                                TextEntry::make('created_at')->label(__('strings.created_at')),
                            ]),
                    ]),


                Section::make(__('strings.branches'))
                    ->schema([
                        TextEntry::make('branches.name') 
                            ->label(__('strings.branch_name'))
                             ->bulleted()
                            ->listWithLineBreaks(),
                    ]),
            ]);
    }
}
