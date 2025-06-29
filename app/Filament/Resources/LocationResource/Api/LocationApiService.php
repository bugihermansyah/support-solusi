<?php
namespace App\Filament\Resources\LocationResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\LocationResource;
use Illuminate\Routing\Router;


class LocationApiService extends ApiService
{
    protected static string | null $resource = LocationResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
