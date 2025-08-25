<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $titolo
 * @property string $descrizione
 * @property string $indirizzo
 * @property float $latitude
 * @property float $longitude
 * @property string $stato
 * @property string|null $orari_apertura
 * @property string|null $prezzo_biglietto
 * @property string|null $sito_web
 * @property string|null $telefono
 * @property string|null $note_visitatori
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'titolo',
        'descrizione',
        'indirizzo',
        'latitude',
        'longitude',
        'stato',
        'orari_apertura',
        'prezzo_biglietto',
        'sito_web',
        'telefono',
        'note_visitatori',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Full-text like search on titolo and indirizzo.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = trim($search);

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('titolo', 'like', "%{$term}%")
                ->orWhere('indirizzo', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by stato if provided and valid.
     */
    public function scopeByStato(Builder $query, ?string $stato): Builder
    {
        $allowed = ['attivo', 'disattivo', 'in_allarme'];
        if ($stato === null || $stato === '' || ! in_array($stato, $allowed, true)) {
            return $query;
        }

        return $query->where('stato', $stato);
    }

    /**
     * Nearby scope using Haversine formula (radius in kilometers).
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm = 10): Builder
    {
        $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';

        return $query->select('*')
            ->selectRaw("{$haversine} as distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }
}
