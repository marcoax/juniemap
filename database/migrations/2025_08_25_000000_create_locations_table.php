<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('titolo')->unique();
            $table->text('descrizione');
            $table->string('indirizzo');
            // Use decimal(10,8) for lat, decimal(11,8) for lng for precision
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('stato', ['attivo', 'disattivo', 'in_allarme'])->default('attivo');
            $table->text('orari_apertura')->nullable();
            $table->string('prezzo_biglietto')->nullable();
            $table->string('sito_web')->nullable();
            $table->string('telefono')->nullable();
            $table->text('note_visitatori')->nullable();
            $table->timestamps();
            $table->index(['stato']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
