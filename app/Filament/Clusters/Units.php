<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Units extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'group-units';
}
