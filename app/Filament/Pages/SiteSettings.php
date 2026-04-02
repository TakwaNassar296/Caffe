<?php

namespace App\Filament\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use App\Traits\GeneralSettings;
use Filament\Forms\Components\Tabs;
use Filament\Actions\LocaleSwitcher;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Illuminate\Contracts\Support\Htmlable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;


class SiteSettings extends Page
{
    public $record;

    use HasPageShield;

    public ?array $data = [];

    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.site-settings-page';

    public static function getNavigationLabel(): string
    {

        return __('strings.settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('strings.settings');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.settings');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(__('strings.settings'))
                    ->schema([
                   

                        FileUpload::make('image')
                            ->label(__('strings.image'))
                            ->image()
                            ->directory('uploads/site_images')
                            ->maxSize(2048)
                            ->rules('image', 'mimes:jpg,jpeg,png,webp')
                            ->required(),


                        FileUpload::make('images')
                            ->label(__('strings.main_banner_images'))
                            ->image()
                            ->directory('uploads/images')
                            ->maxSize(2048)
                            ->multiple()
                            ->rules('image', 'mimes:jpg,jpeg,png,webp')
                            ->required(),

                        TextInput::make('delivery_charge')
                            ->label(__('strings.delivery_charge'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('tax_percentage')
                            ->label(__('strings.tax_percentage'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('free_delivery_minimum')
                            ->label(__('strings.free_delivery_minimum'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Textarea::make('text_cart')
                            ->label(__('strings.cart_page_text'))
                            ->maxLength(65535)
                             ->required()
                            ->columnSpanFull(),

                        Textarea::make('text_order')
                            ->label(__('strings.order_confirmation_page_text'))
                            ->maxLength(65535)
                             ->required()
                            ->columnSpanFull(),
                    ])->columns(2)

            ])->statePath('data');
    }


    public function mount()
    {
        $siteSetting = SiteSetting::first();
        if ($siteSetting) {
            // Decode JSON images for the form
            $images = $siteSetting->images;
            if (is_string($images)) {
                $images = json_decode($images, true) ?: [];
            }
            
            $this->form->fill([
                // 'title' => $siteSetting->title ?? '',
                'image' => $siteSetting->image ? $siteSetting->image : null,
                'images' => $images, // Pass decoded array to form
                // 'description' => $siteSetting->description ?? '',
                'delivery_charge' => $siteSetting->delivery_charge ?? '',
                'tax_percentage' => $siteSetting->tax_percentage ?? '',
                'free_delivery_minimum' => $siteSetting->free_delivery_minimum ?? '',
                'text_cart' => $siteSetting->text_cart ?? '',
                'text_order' => $siteSetting->text_order ?? '',
            ]);
        } else {
            session()->flash('error', __('strings.settings_credentials_not_found'));
        }
    }


    public function submit()
    {
        $data = $this->form->getState();

        $settings = SiteSetting::firstOrNew(['id' => 1]);

        // Convert images array to JSON
        $imagesJson = !empty($data['images']) ? json_encode($data['images']) : null;

        $settings->fill([
            // 'title' => $data['title'],
            // 'description' => $data['description'],
            'image' => $data['image'],
            'images' => $imagesJson, // Save as JSON
            'delivery_charge' => $data['delivery_charge'],
            'tax_percentage' => $data['tax_percentage'],
            'free_delivery_minimum' => $data['free_delivery_minimum'],
            'text_cart' => $data['text_cart'],
            'text_order' => $data['text_order'],
        ]);

        if ($settings->save()) {
            Notification::make()
                ->title(__('strings.settings_updated_successfully'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('strings.settings_update_failed'))
                ->error()
                ->send();
        }
    }


   public static function canAccess(): bool
    {
        return auth('admin')->user()?->super_admin == 1;
    }
}