<?php
namespace App\Filament\Resources\LocationResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filament\Resources\LocationResource;
use App\Filament\Resources\LocationResource\Api\Transformers\LocationTransformer;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = LocationResource::class;
    public static bool $public = true;


    /**
     * List of Location
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function handler()
    {
        $query = static::getEloquentQuery()
            ->with('contracts');

        if (request()->query('per_page') == 'all') {
            return LocationTransformer::collection($query->get());
        }
        
        $query = QueryBuilder::for($query)
        ->allowedFields($this->getAllowedFields() ?? [])
        ->allowedSorts($this->getAllowedSorts() ?? [])
        ->allowedFilters($this->getAllowedFilters() ?? [])
        ->allowedIncludes($this->getAllowedIncludes() ?? [])
        // ->paginate(request()->query('per_page'))
        ->paginate(request()->query('all'))
        ->appends(request()->query());

        return LocationTransformer::collection($query);
    }
}
