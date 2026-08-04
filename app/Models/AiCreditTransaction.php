<?php

namespace App\Models;

use App\Enums\AiCreditTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiCreditTransaction extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['*'];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
        'type' => AiCreditTransactionType::class,
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
