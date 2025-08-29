<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Resources;

use App\Enums\LocationStato;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class LocationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_resource_transformation(): void
    {
        // Arrange
        $location = Location::factory()->create([
            'titolo' => 'Test Location',
            'descrizione' => 'Test Description',
            'indirizzo' => 'Test Address',
            'latitude' => 45.123456,
            'longitude' => 9.654321,
            'stato' => LocationStato::Attivo->value,
            'orari_apertura' => '09:00-18:00',
            'prezzo_biglietto' => '€10',
            'sito_web' => 'https://example.com',
            'telefono' => '+39 123 456 789',
            'note_visitatori' => 'Test notes',
        ]);

        $request = Request::create('/');

        // Act
        $resource = new LocationResource($location);
        $array = $resource->toArray($request);

        // Assert
        $this->assertSame($location->id, $array['id']);
        $this->assertSame('Test Location', $array['titolo']);
        $this->assertSame('Test Description', $array['descrizione']);
        $this->assertSame('Test Address', $array['indirizzo']);
        $this->assertSame(45.123456, $array['latitude']);
        $this->assertSame(9.654321, $array['longitude']);

        // Check stato structure
        $this->assertIsArray($array['stato']);
        $this->assertSame('attivo', $array['stato']['value']);
        $this->assertSame('Attivo', $array['stato']['label']);
        $this->assertSame('#10B981', $array['stato']['color']);
        $this->assertSame('success', $array['stato']['css_class']);

        $this->assertSame('09:00-18:00', $array['orari_apertura']);
        $this->assertSame('€10', $array['prezzo_biglietto']);
        $this->assertSame('https://example.com', $array['sito_web']);
        $this->assertSame('+39 123 456 789', $array['telefono']);
        $this->assertSame('Test notes', $array['note_visitatori']);

        // Check timestamps
        $this->assertNotNull($array['created_at']);
        $this->assertNotNull($array['updated_at']);
    }

    public function test_location_resource_with_null_optional_fields(): void
    {
        // Arrange
        $location = Location::factory()->create([
            'titolo' => 'Test Location',
            'descrizione' => '', // Empty string instead of null since field is not nullable
            'indirizzo' => 'Test Address',
            'orari_apertura' => null,
            'prezzo_biglietto' => null,
            'sito_web' => null,
            'telefono' => null,
            'note_visitatori' => null,
        ]);

        $request = Request::create('/');

        // Act
        $resource = new LocationResource($location);
        $array = $resource->toArray($request);

        // Assert
        $this->assertSame('', $array['descrizione']); // Empty string, not null
        $this->assertNull($array['orari_apertura']);
        $this->assertNull($array['prezzo_biglietto']);
        $this->assertNull($array['sito_web']);
        $this->assertNull($array['telefono']);
        $this->assertNull($array['note_visitatori']);
    }

    public function test_location_resource_with_different_stati(): void
    {
        // Test all possible stati
        $statiTests = [
            ['enum' => LocationStato::Attivo, 'expected' => ['value' => 'attivo', 'label' => 'Attivo', 'color' => '#10B981', 'css_class' => 'success']],
            ['enum' => LocationStato::Disattivo, 'expected' => ['value' => 'disattivo', 'label' => 'Disattivo', 'color' => '#9CA3AF', 'css_class' => 'muted']],
            ['enum' => LocationStato::InAllarme, 'expected' => ['value' => 'in_allarme', 'label' => 'In Allarme', 'color' => '#EF4444', 'css_class' => 'danger']],
        ];

        foreach ($statiTests as $test) {
            // Arrange
            $location = Location::factory()->create([
                'stato' => $test['enum']->value,
            ]);

            $request = Request::create('/');

            // Act
            $resource = new LocationResource($location);
            $array = $resource->toArray($request);

            // Assert
            $this->assertSame($test['expected'], $array['stato']);
        }
    }
}
