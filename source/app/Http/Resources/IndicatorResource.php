<?php

namespace App\Http\Resources;

use App\Core\Indicator\Indicator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicatorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Indicator $indicator */
        $indicator = $this->resource;

        return [
            'code' => $indicator->code,
            'name' => $indicator->name,
            'module' => $indicator->module,
        ];
    }
}
