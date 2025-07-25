<?php
namespace App\Filament\Resources\LocationResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\LocationResource;
use App\Filament\Resources\LocationResource\Api\Requests\CreateLocationRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = LocationResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Location
     *
     * @param CreateLocationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateLocationRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}