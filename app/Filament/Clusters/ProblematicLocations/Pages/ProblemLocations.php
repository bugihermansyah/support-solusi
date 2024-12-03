<?php

namespace App\Filament\Clusters\ProblematicLocations\Pages;

use App\Filament\Clusters\ProblematicLocations;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class ProblemLocations extends Page
{
    use HasPageShield;
    
    protected static string $view = 'filament.clusters.problematic-locations.pages.problem-locations';
    protected static ?string $cluster = ProblematicLocations::class;
    protected static ?string $navigationLabel = 'Problem Locations';
}
