<?php

namespace App\Enums;

enum Unit: string
{
    case Gram = 'g';
    case Kilogram = 'kg';
    case Milliliter = 'ml';
    case Deciliter = 'dcl';
    case Liter = 'l';
    case Piece = 'pcs';
    case Ounce = 'oz';
    case Pound = 'lb';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Map a free-text unit to an allowed value, or null when there is no match.
     */
    public static function normalize(?string $unit): ?string
    {
        if ($unit === null || trim($unit) === '') {
            return null;
        }

        $unit = strtolower(trim($unit));

        if (in_array($unit, self::values(), true)) {
            return $unit;
        }

        return match ($unit) {
            'gram', 'grams', 'g.' => self::Gram->value,
            'kilogram', 'kilograms', 'kg.' => self::Kilogram->value,
            'milliliter', 'milliliters', 'millilitre', 'millilitres' => self::Milliliter->value,
            'deciliter', 'deciliters', 'decilitre', 'decilitres', 'dl' => self::Deciliter->value,
            'liter', 'liters', 'litre', 'litres', 'l.' => self::Liter->value,
            'piece', 'pieces', 'pc', 'pcs.', 'kos', 'kom' => self::Piece->value,
            'ounce', 'ounces', 'oz.' => self::Ounce->value,
            'pound', 'pounds', 'lbs', 'lb.' => self::Pound->value,
            default => null,
        };
    }

    /**
     * Normalize $unit; when it has no allowed equivalent the original wording is
     * appended to $notes so nothing is lost in transit. Returns [$unit, $notes].
     */
    public static function normalizeKeepingNotes(?string $unit, ?string $notes): array
    {
        $normalized = self::normalize($unit);

        if ($normalized === null && $unit !== null && trim($unit) !== '') {
            $unit = trim($unit);
            $notes = $notes === null || trim($notes) === ''
                ? "(unit: {$unit})"
                : rtrim($notes)." (unit: {$unit})";
        }

        return [$normalized, $notes];
    }
}
