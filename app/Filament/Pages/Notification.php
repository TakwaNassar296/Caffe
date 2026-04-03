<?php

namespace App\Filament\Pages;

use App\Models\Admin;
use App\Services\FirebaseNotificationService;
use App\Notifications\NotificationAdmin;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class Notification extends Page
{
    use InteractsWithActions, InteractsWithForms;

    protected static string $view = 'filament.pages.notification';
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    public $template;
    public $sendToAllAdmins = true;

    protected FirebaseNotificationService $firebaseNotificationService;

    public function __construct()
    {
        $this->firebaseNotificationService = new FirebaseNotificationService();
    }

    public function getHeading(): string
    {
        return __('admin.send_notifications');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.notifications');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.notifications');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('template')
                    ->label(__('admin.template_notification'))
                    ->required()
                    ->rows(10),

                Checkbox::make('sendToAllAdmins')
                    ->label(__('admin.send_to_all_admins'))
                    ->default(true), 
            ]);
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();
            
            $this->firebaseNotificationService->sendNotification(
                __('admin.new_notification'),
                $data['template'],
                'all',
                true
            );

            if ($data['sendToAllAdmins'] ?? false) {
                $superAdmins = Admin::where('super_admin', 1)->get();
                
                if ($superAdmins->isNotEmpty()) {
                    NotificationFacade::send($superAdmins, new NotificationAdmin(null, $data['template'], __('admin.notification')));
                }
            }

            FilamentNotification::make()
                ->title(__('admin.notification_sent_successfully'))
                ->success()
                ->send();

            $this->form->fill();

        } catch (\Exception $e) {
            FilamentNotification::make()
                ->title(__('admin.notification_send_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        return auth('admin')->user()?->super_admin == 1;
    }
}