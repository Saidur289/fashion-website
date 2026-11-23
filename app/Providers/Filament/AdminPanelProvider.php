<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\LowStockProductsWidget;
use App\Filament\Widgets\SalesChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Http\Middleware\SetAdminLocale;
use App\Services\Settings;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $colors = Settings::colors();
        $logo = asset(config('safebuy.logo', 'images/brand/safebuy-logo.webp'));
        $favicon = asset(config('safebuy.favicon', 'images/brand/safebuy-logo.webp'));

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(config('safebuy.shop_name', 'SafeBuy').' Admin')
            ->brandLogo($logo)
            ->brandLogoHeight('2.4rem')
            ->favicon($favicon)
            ->colors([
                'primary' => Color::hex($colors['blue']),
                'warning' => Color::hex($colors['orange']),
                'success' => Color::hex('#16A34A'),
                'danger' => Color::hex('#DC2626'),
                'info' => Color::hex($colors['blue']),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<HTML
                    <style>
                        .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-wi-chart, .fi-modal-window {
                            border-radius: 16px !important;
                        }
                        .fi-btn {
                            border-radius: 999px !important;
                        }
                        .fi-input-wrp {
                            border-radius: 12px !important;
                        }
                        .fi-sidebar-item-active > .fi-sidebar-item-btn {
                            background: {$colors['blue_light']} !important;
                            border-radius: 12px !important;
                        }
                    </style>
                HTML)
            )
            ->userMenuItems([
                'locale' => Action::make('switch_locale')
                    ->label(fn () => __('admin.switch_lang'))
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('admin.locale.toggle')),
                'storefront' => Action::make('visit_store')
                    ->label('Visit Storefront')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn () => route('home'))
                    ->openUrlInNewTab(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                SalesChartWidget::class,
                TopProductsWidget::class,
                LowStockProductsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetAdminLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
