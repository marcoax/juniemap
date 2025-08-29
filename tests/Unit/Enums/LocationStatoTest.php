<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\LocationStato;
use PHPUnit\Framework\TestCase;

final class LocationStatoTest extends TestCase
{
    public function test_enum_values_are_correct(): void
    {
        $this->assertSame('attivo', LocationStato::Attivo->value);
        $this->assertSame('disattivo', LocationStato::Disattivo->value);
        $this->assertSame('in_allarme', LocationStato::InAllarme->value);
    }

    public function test_values_method_returns_all_values(): void
    {
        $values = LocationStato::values();

        $this->assertCount(3, $values);
        $this->assertContains('attivo', $values);
        $this->assertContains('disattivo', $values);
        $this->assertContains('in_allarme', $values);
    }

    public function test_try_from_value_with_valid_value(): void
    {
        $stato = LocationStato::tryFromValue('attivo');

        $this->assertInstanceOf(LocationStato::class, $stato);
        $this->assertSame(LocationStato::Attivo, $stato);
    }

    public function test_try_from_value_with_invalid_value(): void
    {
        $stato = LocationStato::tryFromValue('invalid');

        $this->assertNull($stato);
    }

    public function test_try_from_value_with_null(): void
    {
        $stato = LocationStato::tryFromValue(null);

        $this->assertNull($stato);
    }

    public function test_is_valid_with_valid_values(): void
    {
        $this->assertTrue(LocationStato::isValid('attivo'));
        $this->assertTrue(LocationStato::isValid('disattivo'));
        $this->assertTrue(LocationStato::isValid('in_allarme'));
    }

    public function test_is_valid_with_invalid_values(): void
    {
        $this->assertFalse(LocationStato::isValid('invalid'));
        $this->assertFalse(LocationStato::isValid(null));
        $this->assertFalse(LocationStato::isValid(''));
    }

    public function test_label_method(): void
    {
        $this->assertSame('Attivo', LocationStato::Attivo->label());
        $this->assertSame('Disattivo', LocationStato::Disattivo->label());
        $this->assertSame('In Allarme', LocationStato::InAllarme->label());
    }

    public function test_css_class_method(): void
    {
        $this->assertSame('success', LocationStato::Attivo->cssClass());
        $this->assertSame('muted', LocationStato::Disattivo->cssClass());
        $this->assertSame('danger', LocationStato::InAllarme->cssClass());
    }

    public function test_color_method(): void
    {
        $this->assertSame('#10B981', LocationStato::Attivo->color());
        $this->assertSame('#9CA3AF', LocationStato::Disattivo->color());
        $this->assertSame('#EF4444', LocationStato::InAllarme->color());
    }
}
