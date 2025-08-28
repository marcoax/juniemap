<?php

namespace Tests\Feature;

use App\Models\Location;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LocationSeeder::class);
    }

    public function test_index_renders_inertia_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('map'));
    }

    public function test_search_returns_filtered_results(): void
    {
        $first = Location::query()->first();
        $response = $this->getJson('/locations/search?search='.urlencode($first->titolo));
        $response->assertOk();
        $response->assertJsonStructure(['data']);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_details_endpoint_returns_full_location(): void
    {
        $loc = Location::query()->first();
        $response = $this->getJson("/locations/{$loc->id}/details");
        $response->assertOk();
        $response->assertJsonPath('data.id', $loc->id);
        $response->assertJsonPath('data.titolo', $loc->titolo);
        $response->assertJsonPath('data.indirizzo', $loc->indirizzo);
    }
}
