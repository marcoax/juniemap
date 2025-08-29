<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LocationStato;
use App\Models\Location;
// Removed LocationSeeder import as we're not using it
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Don't seed by default, create specific test data in each test
    }

    public function test_index_renders_inertia_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('map'));
    }

    public function test_search_returns_filtered_results(): void
    {
        $location = Location::factory()->create([
            'titolo' => 'Unique Test Location',
            'stato' => LocationStato::Attivo->value,
        ]);

        $response = $this->getJson('/locations/search?search='.urlencode('Unique Test'));

        $response->assertOk();
        $response->assertJsonStructure(['data']);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertSame($location->id, $data[0]['id']);
        $this->assertSame('Unique Test Location', $data[0]['titolo']);
    }

    public function test_details_endpoint_returns_full_location(): void
    {
        $location = Location::factory()->create([
            'titolo' => 'Test Location Details',
            'descrizione' => 'Test Description',
            'stato' => LocationStato::Attivo->value,
        ]);

        $response = $this->getJson("/locations/{$location->id}/details");

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'titolo',
                'descrizione',
                'indirizzo',
                'latitude',
                'longitude',
                'stato' => ['value', 'label', 'color', 'css_class'],
                'created_at',
                'updated_at',
            ],
        ]);

        $data = $response->json('data');
        $this->assertSame($location->id, $data['id']);
        $this->assertSame('Test Location Details', $data['titolo']);
        $this->assertSame('Test Description', $data['descrizione']);
        $this->assertSame('attivo', $data['stato']['value']);
        $this->assertSame('Attivo', $data['stato']['label']);
    }

    public function test_search_with_stato_filter(): void
    {
        Location::factory()->create([
            'titolo' => 'Active Location',
            'stato' => LocationStato::Attivo->value,
        ]);

        Location::factory()->create([
            'titolo' => 'Inactive Location',
            'stato' => LocationStato::Disattivo->value,
        ]);

        $response = $this->getJson('/locations/search?stato=attivo');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Active Location', $data[0]['titolo']);
    }

    public function test_search_with_combined_filters(): void
    {
        Location::factory()->create([
            'titolo' => 'Active Test Location',
            'stato' => LocationStato::Attivo->value,
        ]);

        Location::factory()->create([
            'titolo' => 'Active Other Location',
            'stato' => LocationStato::Attivo->value,
        ]);

        Location::factory()->create([
            'titolo' => 'Test Location Inactive',
            'stato' => LocationStato::Disattivo->value,
        ]);

        $response = $this->getJson('/locations/search?search=Test&stato=attivo');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Active Test Location', $data[0]['titolo']);
    }

    public function test_details_endpoint_returns_404_for_non_existent_location(): void
    {
        $response = $this->getJson('/locations/99999/details');

        $response->assertNotFound();
        $response->assertJsonStructure([
            'message',
            'error',
        ]);
        $response->assertJsonPath('error', 'LOCATION_NOT_FOUND');
    }

    public function test_show_endpoint_returns_location(): void
    {
        $location = Location::factory()->create([
            'titolo' => 'Show Test Location',
        ]);

        $response = $this->getJson("/locations/{$location->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $location->id);
        $response->assertJsonPath('data.titolo', 'Show Test Location');
    }

    public function test_show_endpoint_returns_404_for_non_existent_location(): void
    {
        $response = $this->getJson('/locations/99999');

        $response->assertNotFound();
    }

    public function test_search_validation_rejects_invalid_stato(): void
    {
        $response = $this->getJson('/locations/search?stato=invalid_stato');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['stato']);
    }

    public function test_search_handles_empty_results(): void
    {
        $response = $this->getJson('/locations/search?search=NonExistentLocation');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEmpty($data);
    }
}
