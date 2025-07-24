<?php
namespace App\Filament\Resources\DiskUsageResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\DiskUsage;

/**
 * @property DiskUsage $resource
 */
class DiskUsageTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}
