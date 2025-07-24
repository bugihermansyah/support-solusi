<?php
namespace App\Filament\Resources\DiskUsageResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\DiskUsageResource;
use Illuminate\Routing\Router;


class DiskUsageApiService extends ApiService
{
    protected static string | null $resource = DiskUsageResource::class;

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
