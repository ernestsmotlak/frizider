<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeIngredient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'name',
        'quantity',
        'unit',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'sort_order' => 'number',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
