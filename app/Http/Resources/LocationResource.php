<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Location
 */
final class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titolo' => $this->titolo,
            'descrizione' => $this->descrizione,
            'indirizzo' => $this->indirizzo,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'stato' => [
                'value' => $this->stato->value,
                'label' => $this->stato->label(),
                'color' => $this->stato->color(),
                'css_class' => $this->stato->cssClass(),
            ],
            'orari_apertura' => $this->orari_apertura,
            'prezzo_biglietto' => $this->prezzo_biglietto,
            'sito_web' => $this->sito_web,
            'telefono' => $this->telefono,
            'note_visitatori' => $this->note_visitatori,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
