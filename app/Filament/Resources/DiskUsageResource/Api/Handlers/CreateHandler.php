<?php
namespace App\Filament\Resources\DiskUsageResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DiskUsageResource;
use App\Filament\Resources\DiskUsageResource\Api\Requests\CreateDiskUsageRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = DiskUsageResource::class;
    public static bool $public = true;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create DiskUsage
     *
     * @param CreateDiskUsageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateDiskUsageRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}