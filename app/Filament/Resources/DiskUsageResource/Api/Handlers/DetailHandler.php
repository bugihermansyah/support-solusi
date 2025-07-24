<?php

namespace App\Filament\Resources\DiskUsageResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\DiskUsageResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\DiskUsageResource\Api\Transformers\DiskUsageTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = DiskUsageResource::class;


    /**
     * Show DiskUsage
     *
     * @param Request $request
     * @return DiskUsageTransformer
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');
        
        $query = static::getEloquentQuery();

        $query = QueryBuilder::for(
            $query->where(static::getKeyName(), $id)
        )
            ->first();

        if (!$query) return static::sendNotFoundResponse();

        return new DiskUsageTransformer($query);
    }
}
