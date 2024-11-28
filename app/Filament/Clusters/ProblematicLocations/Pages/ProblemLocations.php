<?php

namespace App\Filament\Clusters\ProblematicLocations\Pages;

use App\Filament\Clusters\ProblematicLocations;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class ProblemLocations extends Page
{
    use HasPageShield;
    // protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.clusters.problematic-locations.pages.problem-locations';
    // protected static ?string $cluster = ProblematicLocations::class;
    protected static ?string $navigationLabel = 'Problem Locations';
    protected static ?string $navigationGroup = 'Clusters';
    // protected static ?string $navigationIcon = 'heroicon-o-location-marker';  // Icon menu

    // public function render(): View
    // {
    //     return view('filament.clusters.problematic-locations.pages.problem-locations');
    // }
}
