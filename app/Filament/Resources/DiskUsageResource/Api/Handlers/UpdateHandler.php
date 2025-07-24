<?php
namespace App\Filament\Resources\DiskUsageResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DiskUsageResource;
use App\Filament\Resources\DiskUsageResource\Api\Requests\UpdateDiskUsageRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = DiskUsageResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update DiskUsage
     *
     * @param UpdateDiskUsageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateDiskUsageRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}