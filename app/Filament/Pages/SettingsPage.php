<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Services\ImageOptimizer;
use App\Services\Settings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SettingsPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings-page';

    public ?array $data = [];

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.settings');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSettings() ?? false;
    }

    public function mount(): void
    {
        $colors = Settings::colors();

        $this->form->fill([
            // Store
            'shop_name' => Settings::get('shop_name', config('app.name', 'SafeBuy')),
            'topbar_text' => Settings::get('topbar_text', 'Welcome to SafeBuy — 100% authentic K-Beauty & smart gadgets, delivered to your door'),
            'logo' => Settings::get('logo', 'images/brand/safebuy-logo.webp'),
            'favicon' => Settings::get('favicon', 'images/brand/safebuy-logo.webp'),
            'email' => Settings::get('email', config('safebuy.email')),
            'phone' => Settings::get('phone', config('safebuy.phone')),
            'address' => Settings::get('address', config('safebuy.address')),
            'hours' => Settings::get('hours', config('safebuy.hours')),
            'budget_limit' => (int) Settings::get('budget_limit', config('safebuy.budget_limit', 1000)),
            'social_facebook' => Settings::get('social_facebook', ''),
            'social_instagram' => Settings::get('social_instagram', ''),
            'social_youtube' => Settings::get('social_youtube', ''),
            'social_whatsapp' => Settings::get('social_whatsapp', ''),

            // Colors
            'color_blue' => $colors['blue'],
            'color_blue_dark' => $colors['blue_dark'],
            'color_blue_light' => $colors['blue_light'],
            'color_orange' => $colors['orange'],
            'color_orange_dark' => $colors['orange_dark'],
            'color_orange_light' => $colors['orange_light'],

            // Payment
            'cod_enabled' => (bool) config('safebuy.cod_enabled', true),
            'bkash_enabled' => (bool) config('safebuy.bkash.enabled', true),
            'bkash_number' => Settings::get('bkash_number', config('safebuy.bkash.number')),
            'bkash_type' => Settings::get('bkash_type', config('safebuy.bkash.type', 'personal')),

            // Delivery
            'shipping_inside_dhaka' => (int) config('safebuy.shipping.inside_dhaka', 70),
            'shipping_outside_dhaka' => (int) config('safebuy.shipping.outside_dhaka', 130),
            'shipping_free_over' => (int) config('safebuy.shipping.free_over', 3000),
            'shipping_pickup_enabled' => (bool) config('safebuy.shipping.pickup_enabled', false),
            'shipping_pickup_address' => Settings::get('shipping_pickup_address', config('safebuy.shipping.pickup_address')),

            // SMS & Notifications
            'notify_email' => Settings::get('notify_email', config('safebuy.notify_email')),
            'notify_phone' => Settings::get('notify_phone', config('safebuy.notify_phone')),
            'sms_driver' => Settings::get('sms_driver', config('safebuy.sms.driver', 'log')),
            'sms_url' => Settings::get('sms_url', config('safebuy.sms.url')),
            'sms_method' => Settings::get('sms_method', config('safebuy.sms.method', 'GET')),
            'sms_params' => Settings::get('sms_params', config('safebuy.sms.params')),
            'sms_api_key' => Settings::get('sms_api_key', config('safebuy.sms.api_key')),
            'sms_sender_id' => Settings::get('sms_sender_id', config('safebuy.sms.sender_id')),
            'sms_tpl_confirmed' => Settings::smsTemplate('confirmed'),
            'sms_tpl_shipped' => Settings::smsTemplate('shipped'),
            'sms_tpl_delivered' => Settings::smsTemplate('delivered'),
            'sms_tpl_cancelled' => Settings::smsTemplate('cancelled'),
            'sms_tpl_returned' => Settings::smsTemplate('returned'),

            // Mail & Reviews
            'mail_mailer' => Settings::get('mail_mailer', config('mail.default', 'log')),
            'mail_host' => Settings::get('mail_host', config('mail.mailers.smtp.host')),
            'mail_port' => Settings::get('mail_port', config('mail.mailers.smtp.port')),
            'mail_username' => Settings::get('mail_username', config('mail.mailers.smtp.username')),
            'mail_password' => Settings::get('mail_password', ''),
            'mail_from_address' => Settings::get('mail_from_address', config('mail.from.address')),
            'mail_from_name' => Settings::get('mail_from_name', config('mail.from.name')),
            'reviews_auto_approve' => (bool) config('safebuy.reviews_auto_approve', false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Store Info')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Section::make('Shop Details')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('shop_name')->label('Shop Name')->required(),
                                        TextInput::make('email')->label('Support Email')->email(),
                                        TextInput::make('topbar_text')->label('Top Announcement Bar Text')->columnSpanFull(),
                                        TextInput::make('phone')->label('Phone / Hotline')->placeholder('01XXXXXXXXX'),
                                        TextInput::make('hours')->label('Open Hours')->placeholder('Sat–Thu, 10am–8pm'),
                                        TextInput::make('address')->label('Store Address')->columnSpanFull(),
                                        TextInput::make('budget_limit')->label('Homepage "Under Tk X" Limit')->numeric(),
                                        FileUpload::make('logo')->label('Shop Logo')->disk('public_root')->directory('images/uploads/brand')->image(),
                                        FileUpload::make('favicon')->label('Favicon')->disk('public_root')->directory('images/uploads/brand')->image(),
                                    ]),
                                Section::make('Social Links')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('social_facebook')->label('Facebook URL')->url(),
                                        TextInput::make('social_instagram')->label('Instagram URL')->url(),
                                        TextInput::make('social_youtube')->label('YouTube URL')->url(),
                                        TextInput::make('social_whatsapp')->label('WhatsApp Number / Link'),
                                    ]),
                            ]),

                        Tab::make('Site Colors')
                            ->icon('heroicon-o-swatch')
                            ->schema([
                                Section::make('Brand Colors (Applied to Storefront & Admin)')
                                    ->description('Default SafeBuy palette: Blue #0A449B and Action Orange #F97316.')
                                    ->columns(3)
                                    ->schema([
                                        ColorPicker::make('color_blue')->label('Primary Blue')->required(),
                                        ColorPicker::make('color_blue_dark')->label('Dark Blue (Hover)')->required(),
                                        ColorPicker::make('color_blue_light')->label('Light Blue (Surface)')->required(),
                                        ColorPicker::make('color_orange')->label('Action Orange (Buttons & Price)')->required(),
                                        ColorPicker::make('color_orange_dark')->label('Dark Orange (Hover)')->required(),
                                        ColorPicker::make('color_orange_light')->label('Light Orange (Banner)')->required(),
                                    ]),
                            ]),

                        Tab::make('Payment & Delivery')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('Payment Methods')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('cod_enabled')->label('Enable Cash on Delivery (COD)'),
                                        Toggle::make('bkash_enabled')->label('Enable bKash'),
                                        TextInput::make('bkash_number')->label('bKash Number')->placeholder('01XXXXXXXXX'),
                                        Select::make('bkash_type')
                                            ->label('bKash Account Type')
                                            ->options([
                                                'personal' => 'Personal (Send Money)',
                                                'agent' => 'Agent (Cash Out)',
                                                'merchant' => 'Merchant (Payment)',
                                            ]),
                                    ]),
                                Section::make('Default Delivery Charges & Free Shipping')
                                    ->description('For district-specific rates, use Delivery Zones in the sidebar.')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('shipping_inside_dhaka')->label('Inside Dhaka (Tk)')->numeric()->required(),
                                        TextInput::make('shipping_outside_dhaka')->label('Outside Dhaka (Tk)')->numeric()->required(),
                                        TextInput::make('shipping_free_over')->label('Free Delivery Over (Tk, 0 = off)')->numeric()->required(),
                                        Toggle::make('shipping_pickup_enabled')->label('Allow Store Pickup (Free)'),
                                        TextInput::make('shipping_pickup_address')->label('Pickup Address')->columnSpan(2),
                                    ]),
                            ]),

                        Tab::make('SMS & Alerts')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->schema([
                                Section::make('New Order Alerts')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('notify_email')->label('Alert Email')->email(),
                                        TextInput::make('notify_phone')->label('Alert Mobile Number')->placeholder('01XXXXXXXXX'),
                                    ]),
                                Section::make('SMS Gateway (BulkSMSBD / SSL Wireless / Alpha SMS)')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('sms_driver')
                                            ->label('SMS Driver')
                                            ->options([
                                                'log' => 'Log Only (Safe test mode)',
                                                'http' => 'Live HTTP Gateway',
                                            ]),
                                        Select::make('sms_method')->label('HTTP Method')->options(['GET' => 'GET', 'POST' => 'POST']),
                                        TextInput::make('sms_url')->label('Gateway API URL')->columnSpanFull(),
                                        TextInput::make('sms_params')->label('Params Template ({api_key}, {sender_id}, {to}, {message})')->columnSpanFull(),
                                        TextInput::make('sms_api_key')->label('API Key')->password()->revealable(),
                                        TextInput::make('sms_sender_id')->label('Sender ID'),
                                    ]),
                                Section::make('SMS Message Templates')
                                    ->description('Placeholders: {number}, {name}, {total}, {status}, {tracking}, {track_url}')
                                    ->columns(2)
                                    ->schema([
                                        Textarea::make('sms_tpl_confirmed')->label('When Confirmed')->rows(2),
                                        Textarea::make('sms_tpl_shipped')->label('When Shipped')->rows(2),
                                        Textarea::make('sms_tpl_delivered')->label('When Delivered')->rows(2),
                                        Textarea::make('sms_tpl_cancelled')->label('When Cancelled')->rows(2),
                                        Textarea::make('sms_tpl_returned')->label('When Returned')->rows(2),
                                    ]),
                            ]),

                        Tab::make('Email & Reviews')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make('Email / SMTP')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('mail_mailer')->label('Mail Driver')->options(['log' => 'Log Only', 'smtp' => 'SMTP']),
                                        TextInput::make('mail_host')->label('SMTP Host'),
                                        TextInput::make('mail_port')->label('SMTP Port')->numeric(),
                                        TextInput::make('mail_username')->label('SMTP Username'),
                                        TextInput::make('mail_password')->label('SMTP Password')->password()->revealable(),
                                        TextInput::make('mail_from_address')->label('From Email')->email(),
                                        TextInput::make('mail_from_name')->label('From Name'),
                                    ]),
                                Section::make('Customer Reviews')
                                    ->schema([
                                        Toggle::make('reviews_auto_approve')->label('Auto-approve new reviews (without admin moderation)'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetColors')
                ->label('Reset Default Colors')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (): void {
                    Settings::setMany([
                        'color_blue' => Settings::DEFAULT_COLORS['blue'],
                        'color_blue_dark' => Settings::DEFAULT_COLORS['blue_dark'],
                        'color_blue_light' => Settings::DEFAULT_COLORS['blue_light'],
                        'color_orange' => Settings::DEFAULT_COLORS['orange'],
                        'color_orange_dark' => Settings::DEFAULT_COLORS['orange_dark'],
                        'color_orange_light' => Settings::DEFAULT_COLORS['orange_light'],
                    ], 'branding');
                    $this->mount();
                    Notification::make()->title('Default SafeBuy blue & orange colors restored')->success()->send();
                }),
        ];
    }

    public function save(): void
    {
        abort_unless(self::canAccess(), 403);

        $state = $this->form->getState();

        foreach (['logo', 'favicon'] as $imgKey) {
            if (! empty($state[$imgKey]) && is_string($state[$imgKey])) {
                $state[$imgKey] = ImageOptimizer::toWebp($state[$imgKey]);
            }
        }

        // Preserve existing mail password if left blank
        if (empty($state['mail_password'])) {
            unset($state['mail_password']);
        }

        Settings::setMany($state, 'general');
        ActivityLog::record('settings_updated', 'Updated shop settings & branding configuration');

        Notification::make()->title('Shop settings saved')->success()->send();
    }
}
