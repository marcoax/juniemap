<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LocationStato;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property string $titolo
 * @property string $descrizione
 * @property string $indirizzo
 * @property float $latitude
 * @property float $longitude
 * @property LocationStato $stato
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
            'stato' => LocationStato::class,
        ];
    }

    /**
     * Full-text search on titolo and indirizzo with optimization.
     * Uses full-text search for MySQL if available, fallback to LIKE for other databases or when full-text fails.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = trim($search);

        // Use full-text search for MySQL if available, with fallback
        if ($query->getConnection()->getDriverName() === 'mysql') {
            try {
                // Check if we can use full-text search
                $hasFullTextIndex = $this->hasFullTextIndex($query->getConnection());

                if ($hasFullTextIndex) {
                    return $query->where(function (Builder $q) use ($term): void {
                        $q->whereRaw(
                            'MATCH(titolo, indirizzo) AGAINST(? IN BOOLEAN MODE)',
                            ["+{$term}*"]
                        )->orWhere(function (Builder $subQ) use ($term): void {
                            // Additional LIKE fallback for terms that don't work well with full-text
                            $subQ->where('titolo', 'like', "{$term}%")
                                ->orWhere('indirizzo', 'like', "{$term}%");
                        });
                    });
                }
            } catch (\Exception $e) {
                // Log the error but continue with LIKE search
                \Log::info('Full-text search failed, falling back to LIKE: '.$e->getMessage());
            }
        }

        // Fallback to LIKE search for other databases or when full-text fails
        return $query->where(function (Builder $q) use ($term): void {
            $q->where('titolo', 'like', "{$term}%")
                ->orWhere('indirizzo', 'like', "{$term}%")
                ->orWhere('titolo', 'like', "%{$term}%")
                ->orWhere('indirizzo', 'like', "%{$term}%");
        });
    }

    /**
     * Check if full-text index exists for the locations table.
     */
    private function hasFullTextIndex($connection): bool
    {
        static $hasIndex = null;

        if ($hasIndex === null) {
            try {
                $indexes = $connection->select(
                    "SHOW INDEX FROM locations WHERE Index_type = 'FULLTEXT' AND Column_name IN ('titolo', 'indirizzo')"
                );
                $hasIndex = ! empty($indexes);
            } catch (\Exception $e) {
                $hasIndex = false;
            }
        }

        return $hasIndex;
    }

    /**
     * Filter by stato if provided and valid.
     */
    public function scopeByStato(Builder $query, LocationStato|string|null $stato): Builder
    {
        if ($stato === null || $stato === '') {
            return $query;
        }

        // Handle string input by converting to enum
        if (is_string($stato)) {
            $statoEnum = LocationStato::tryFromValue($stato);
            if ($statoEnum === null) {
                return $query;
            }
            $stato = $statoEnum;
        }

        return $query->where('stato', $stato->value);
    }

    /**
     * Nearby scope using Haversine formula (radius in kilometers).
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm = 10.0): Builder
    {
        // For SQLite in tests, use a simpler bounding box approach
        if ($query->getConnection()->getDriverName() === 'sqlite') {
            $latRange = $radiusKm / 111.0; // Rough conversion: 1 degree ≈ 111 km
            $lngRange = $radiusKm / (111.0 * cos(deg2rad($lat)));

            return $query->whereBetween('latitude', [$lat - $latRange, $lat + $latRange])
                ->whereBetween('longitude', [$lng - $lngRange, $lng + $lngRange])
                ->orderBy('latitude');
        }

        // For MySQL, use proper Haversine formula with raw select
        $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';

        return $query->selectRaw('*, '.$haversine.' as distance', [$lat, $lng, $lat])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    /**
     * Get locations by status type.
     *
     * @return Collection<int, Location>
     */
    public static function getByStato(LocationStato $stato): Collection
    {
        return static::query()->where('stato', $stato->value)->get();
    }

    /**
     * Get active locations.
     *
     * @return Collection<int, Location>
     */
    public static function getActive(): Collection
    {
        return static::getByStato(LocationStato::Attivo);
    }

    /**
     * Check if location is active.
     */
    public function isActive(): bool
    {
        return $this->stato === LocationStato::Attivo;
    }

    /**
     * Check if location is in alarm state.
     */
    public function isInAlarm(): bool
    {
        return $this->stato === LocationStato::InAllarme;
    }

    /**
     * Scope for efficient map data retrieval.
     * Selects only essential fields for map display.
     */
    public function scopeForMap(Builder $query): Builder
    {
        return $query->select([
            'id', 'titolo', 'indirizzo', 'latitude', 'longitude', 'stato',
        ]);
    }

    /**
     * Scope for locations within a bounding box (more efficient than radius for large areas).
     */
    public function scopeWithinBounds(Builder $query, float $minLat, float $minLng, float $maxLat, float $maxLng): Builder
    {
        return $query->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng]);
    }

    /**
     * Scope for recent locations (created or updated within timeframe).
     */
    public function scopeRecentlyModified(Builder $query, int $hours = 24): Builder
    {
        $since = now()->subHours($hours);

        return $query->where(function (Builder $q) use ($since): void {
            $q->where('created_at', '>=', $since)
                ->orWhere('updated_at', '>=', $since);
        });
    }

    /**
     * Get the cache tags for this model.
     *
     * @return array<string>
     */
    public function getCacheTags(): array
    {
        return [
            'locations',
            "location.{$this->id}",
            "location.stato.{$this->stato->value}",
        ];
    }
}
