<?php

namespace App\Filament\Resources\LocationResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\LocationResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\LocationResource\Api\Transformers\LocationTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = LocationResource::class;


    /**
     * Show Location
     *
     * @param Request $request
     * @return LocationTransformer
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

        return new LocationTransformer($query);
    }
}
