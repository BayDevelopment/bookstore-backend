<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomRequestPasswordReset;
use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset(CustomRequestPasswordReset::class) // ✅ cukup ini
            ->font('poppins')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn() => new HtmlString('
                <div style="text-align:center; margin-top:20px; font-size:12px; color:#6b7280;">
                    Developed by <strong>Bayu Albar Ladici</strong>
                </div>
            ')
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER,
                fn() => new HtmlString('
                <div style="text-align:center; margin-top:20px; font-size:12px; color:#6b7280;">
                    Developed by <strong>Bayu Albar Ladici</strong>
                </div>
            ')
            )
            ->brandName(new HtmlString('
                <span style="font-style: italic; font-weight: 400; color: #f97316;">
                    BookStore
                </span>
                <span class="italic font-semibold ml-1 text-black dark:text-white">
                    Panel
                </span>
            '))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsDashboard::class,
                \App\Filament\Widgets\RevenueChart::class,
                \App\Filament\Widgets\CustomerChart::class,
                \App\Filament\Widgets\TopProductChart::class,
                \App\Filament\Widgets\RecentOrders::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class, // ✅ diperbaiki
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
