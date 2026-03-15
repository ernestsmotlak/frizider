<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAiRecipeLog extends Model
{
    protected $table = 'user_ai_recipe_logs';

    protected $fillable = [
        'user_id',
        'recipe_id',
        'source_recipe_id',
        'action',
        'request_meta',
        'success',
        'error_message',
        'tokens_used',
        'idempotency_key',
    ];

    protected $casts = [
        'request_meta' => 'array',
        'success' => 'boolean',
        'tokens_used' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function sourceRecipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'source_recipe_id');
    }
}
