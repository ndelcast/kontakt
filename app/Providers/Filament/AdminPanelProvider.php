<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->profile(EditProfile::class)
            ->brandName('Kontak')
            ->font('Inter')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
                'danger' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Sky,
            ])
            ->navigationGroups([
                NavigationGroup::make(__('Pipeline'))
                    ->icon('heroicon-o-chart-bar'),
                NavigationGroup::make(__('Contacts'))
                    ->icon('heroicon-o-user-group'),
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn () => new HtmlString('
                    <script>
                        // Force sidebar persisted state to closed before Alpine reads it
                        localStorage.setItem("isOpen", JSON.stringify(false));

                        // After Alpine inits, override the sidebar store so it can never open
                        document.addEventListener("alpine:init", () => {
                            const original = Alpine.store.bind(Alpine);
                            const patchApplied = { value: false };

                            // Use Alpine.effect to watch and revert any open attempt
                            document.addEventListener("alpine:initialized", () => {
                                Alpine.effect(() => {
                                    const sidebar = Alpine.store("sidebar");
                                    if (sidebar && sidebar.isOpen) {
                                        sidebar.isOpen = false;
                                    }
                                });
                                // Also replace open() with a no-op
                                const sidebar = Alpine.store("sidebar");
                                if (sidebar) {
                                    sidebar.open = function() {};
                                }
                            });
                        });
                    </script>
                    <style>
                        /* Hide all sidebar toggle/expand/collapse buttons */
                        .fi-topbar-open-sidebar-btn,
                        .fi-topbar-close-sidebar-btn,
                        .fi-sidebar-close-overlay {
                            display: none !important;
                        }
                        /* Hide the chevron expand button inside the sidebar header */
                        .fi-sidebar-header .fi-icon-btn[x-on\:click="$store.sidebar.open()"] {
                            display: none !important;
                        }
                        /* Hide the chevron collapse button inside the sidebar header */
                        .fi-sidebar-header .fi-icon-btn[x-on\:click="$store.sidebar.close()"] {
                            display: none !important;
                        }
                    </style>
                '),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn () => new HtmlString('
                    <a href="' . url(filament()->getHomeUrl() ?? '/admin') . '" class="me-4 hidden lg:flex items-center shrink-0">
                        <span class="text-lg font-bold text-gray-950 dark:text-white">Kontak</span>
                    </a>
                '),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
