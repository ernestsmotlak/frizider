<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeInstruction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'instruction',
        'sort_order',
        'completed',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'completed' => 'boolean',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
