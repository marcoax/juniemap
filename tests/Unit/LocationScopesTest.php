<?php

namespace Tests\Unit;

use App\Models\Location;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationScopesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LocationSeeder::class);
    }

    public function test_scope_search_matches_title(): void
    {
        $first = Location::query()->firstOrFail();
        $count = Location::query()->search($first->titolo)->count();
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function test_scope_by_stato_filters(): void
    {
        $all = Location::query()->count();
        $attivi = Location::query()->byStato('attivo')->count();
        $this->assertLessThanOrEqual($all, $attivi);
    }

    public function test_scope_search_does_not_match_descrizione(): void
    {
        $first = Location::query()->firstOrFail();

        // Create a unique token and ensure it's present only in descrizione
        $unique = 'ONLY_IN_DESCRIPTION_'.uniqid();
        $first->descrizione = trim(($first->descrizione ?? '').' '.$unique);
        $first->save();

        $count = Location::query()->search($unique)->count();
        $this->assertSame(0, $count);
    }
}
