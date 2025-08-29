<?php

declare(strict_types=1);

namespace App\Enums;

enum LocationStato: string
{
    case Attivo = 'attivo';
    case Disattivo = 'disattivo';
    case InAllarme = 'in_allarme';

    /**
     * Get all enum values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get enum case from value with fallback.
     */
    public static function tryFromValue(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }

    /**
     * Check if a value is valid for this enum.
     */
    public static function isValid(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        return self::tryFrom($value) !== null;
    }

    /**
     * Get display label for the enum case.
     */
    public function label(): string
    {
        return match ($this) {
            self::Attivo => 'Attivo',
            self::Disattivo => 'Disattivo',
            self::InAllarme => 'In Allarme',
        };
    }

    /**
     * Get CSS class for styling based on status.
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::Attivo => 'success',
            self::Disattivo => 'muted',
            self::InAllarme => 'danger',
        };
    }

    /**
     * Get color hex value for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Attivo => '#10B981',
            self::Disattivo => '#9CA3AF',
            self::InAllarme => '#EF4444',
        };
    }
}
