<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'titolo' => $this->faker->streetName(),
            'descrizione' => $this->faker->paragraph(),
            'indirizzo' => $this->faker->address(),
            'latitude' => $this->faker->latitude(36.6, 46.5),
            'longitude' => $this->faker->longitude(6.6, 18.5),
            'stato' => $this->faker->randomElement(['attivo', 'disattivo', 'in_allarme']),
            'orari_apertura' => '09:00 - 18:00',
            'prezzo_biglietto' => '€10',
            'sito_web' => $this->faker->url(),
            'telefono' => $this->faker->phoneNumber(),
            'note_visitatori' => $this->faker->sentence(),
        ];
    }
}
