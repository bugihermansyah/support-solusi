<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Livewire\MyProfileExtended;
use App\Settings\GeneralSettings;
use Awcodes\FilamentGravatar\GravatarPlugin;
use Awcodes\FilamentGravatar\GravatarProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Kenepa\Banner\BannerPlugin;
use Shanerbaner82\PanelRoles\PanelRoles;

class SupportPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('support')
            ->path('sp')
            ->login(Login::class)
            ->defaultAvatarProvider(GravatarProvider::class)
            ->globalSearch(false)
            ->favicon(fn (GeneralSettings $settings) => Storage::url($settings->site_favicon))
            ->brandName(fn (GeneralSettings $settings) => $settings->brand_name)
            ->brandLogo(fn (GeneralSettings $settings) => Storage::url($settings->brand_logo))
            ->brandLogoHeight(fn (GeneralSettings $settings) => $settings->brand_logoHeight)
            ->colors([
                'primary' => Color::Cyan,
            ])
            ->databaseNotifications()->databaseNotificationsPolling('30s')
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Support/Resources'), for: 'App\\Filament\\Support\\Resources')
            ->resources([
                config('filament-logger.activity_resource')
            ])
            ->discoverPages(in: app_path('Filament/Support/Pages'), for: 'App\\Filament\\Support\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Support/Widgets'), for: 'App\\Filament\\Support\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationItems([
                NavigationItem::make('Buku Mantra Sihir')
                    ->url('https://supportx.gitbook.io/buku-mantra-sihir', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-book-open')
                    ->group('Dokumentasi')
                    ->sort(3),
                NavigationItem::make('Alat Tempur')
                    ->url('https://drive.google.com/drive/folders/17AXS4HOwaFD8UQZCo4MFmfUTk2_JkRk8', shouldOpenInNewTab: true)
                    ->icon('heroicon-c-cube')
                    ->group('Dokumentasi')
                    ->sort(4),
            ])
            ->plugins([
                BannerPlugin::make()
                    ->disableBannerManager(),
                GravatarPlugin::make()
                    ->default('robohash')
                    ->size(200)
                    ->rating('pg'),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 2,
                        'sm' => 1
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                \Jeffgreco13\FilamentBreezy\BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        navigationGroup: 'Settings',
                        hasAvatars: true,
                        slug: 'my-profile'
                    )
                    ->myProfileComponents([
                        'personal_info' => MyProfileExtended::class,
                    ]),
            ])
            ->plugin(PanelRoles::make()
                ->roleToAssign('staff')
                ->restrictedRoles(['staff']),
            );
    }
}
