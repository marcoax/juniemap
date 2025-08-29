<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table): void {
            // Add indexes for search optimization
            $table->index(['titolo']);
            $table->index(['indirizzo']);

            // Composite index for common search patterns
            $table->index(['stato', 'titolo']);

            // Full-text index for search if using MySQL 5.7+
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['titolo', 'indirizzo']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table): void {
            $table->dropIndex(['titolo']);
            $table->dropIndex(['indirizzo']);
            $table->dropIndex(['stato', 'titolo']);

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->dropFullText(['titolo', 'indirizzo']);
            }
        });
    }
};
