<?php

namespace App\Services;

use App\Enums\Unit;
use App\Models\PantryItem;

class PantryIntakeService
{
    /**
     * The single door into the pantry — manual creates, conversions, and future
     * scanners all add rows through here so normalization lives in one place.
     */
    public function add(int $userId, array $attributes): PantryItem
    {
        [$unit, $notes] = Unit::normalizeKeepingNotes(
            $attributes['unit'] ?? null,
            $attributes['notes'] ?? null,
        );

        return PantryItem::create([
            'user_id' => $userId,
            'space_id' => $attributes['space_id'] ?? null,
            'name' => trim($attributes['name']),
            'quantity' => $attributes['quantity'] ?? null,
            'unit' => $unit,
            'notes' => $notes,
            'expiry_date' => $attributes['expiry_date'] ?? null,
            'purchase_date' => $attributes['purchase_date'] ?? null,
        ]);
    }
}
