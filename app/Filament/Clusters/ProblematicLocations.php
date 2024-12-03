<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class ProblematicLocations extends Cluster
{
    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'problematic-locations';
}
