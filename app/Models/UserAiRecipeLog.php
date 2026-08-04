<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AiGenerationStatus;

class UserAiRecipeLog extends Model
{
    protected $table = 'user_ai_recipe_logs';

    protected $fillable = [
        'user_id',
        'recipe_id',
        'source_recipe_id',
        'action',
        'status',
        'request_meta',
        'error_message',
        'tokens_used',
        'idempotency_key',
        'completed_at',
    ];

    protected $casts = [
        'request_meta' => 'array',
        'status' => AiGenerationStatus::class,
        'tokens_used' => 'integer',
        'completed_at' => 'datetime',
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
