<?php

namespace App\Providers\Filament;

use App\Filament\Monitoring\Widgets\AdvancedStatsOverviewWidget;
use App\Filament\Monitoring\Widgets\DailyChartWidget;
use App\Filament\Monitoring\Widgets\MonitoringTableWidget;
use App\Filament\Monitoring\Widgets\Top5Units;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MonitoringPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('monitoring')
            ->path('monitoring')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigation(false)
            ->maxContentWidth(MaxWidth::Full)
            ->defaultThemeMode(ThemeMode::Dark)
            ->font('Poppins')
            ->brandName('Support Monitoring | PT. SAP')
            ->discoverResources(in: app_path('Filament/Monitoring/Resources'), for: 'App\\Filament\\Monitoring\\Resources')
            ->discoverPages(in: app_path('Filament/Monitoring/Pages'), for: 'App\\Filament\\Monitoring\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Monitoring/Widgets'), for: 'App\\Filament\\Monitoring\\Widgets')
            ->widgets([
                AdvancedStatsOverviewWidget::class,
                DailyChartWidget::class,
                MonitoringTableWidget::class,
                Top5Units::class
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
                // Authenticate::class,
            ]);
    }
}
