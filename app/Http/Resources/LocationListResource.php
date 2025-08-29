<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Location
 */
final class LocationListResource extends JsonResource
{
    /**
     * Transform the resource into an array for list views.
     * This resource includes only essential fields for map/list display.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titolo' => $this->titolo,
            'indirizzo' => $this->indirizzo,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'stato' => $this->stato->value,
        ];
    }
}
