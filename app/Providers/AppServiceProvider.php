<?php

namespace App\Providers;

use App\Http\Responses\StaffLoginResponse;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, StaffLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->emptyStateHeading('Belum ada data nih.')
                ->striped()
                ->defaultPaginationPageOption(10)
                ->paginated([5, 10, 25, 50])
                ->extremePaginationLinks();
                // ->defaultSort('created_at', 'desc');
        });
        FilamentShield::configurePermissionIdentifierUsing(
            fn($resource) => str($resource::getModel())
                ->afterLast('\\')
                ->lower()
                ->toString()
        );
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->simple()
                ->labels([
                    'admin' => 'Head',
                    'support' => 'Support'
                ])
                // ->canSwitchPanels(fn (): bool => auth()->user()?->can('switch_panels'))
                ->visible(fn (): bool => auth()->user()?->hasAnyRole([
                    'head',
                ]));
        });
    }
}
