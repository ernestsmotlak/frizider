<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'servings',
        'prep_time',
        'cook_time',
        'image_url',
        'is_ai_generated',
    ];

    protected $casts = [
        'servings' => 'integer',
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'is_ai_generated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function recipeInstructions()
    {
        return $this->hasMany(RecipeInstruction::class);
    }

    public function userAiRecipeLogs()
    {
        return $this->hasMany(UserAiRecipeLog::class, 'recipe_id');
    }
}
