<?php
namespace App\Filament\Resources\LocationResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Location;

/**
 * @property Location $resource
 */
class LocationTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return $this->resource->toArray();
        return [
        'id' => $this->id,
        'company_id' => $this->company_id,
        'name' => $this->name,
        'latitude' => $this->latitude,
        'longitude' => $this->longitude,

        'contracts' => $this->whenLoaded('contracts'),
        ];
    }
}
