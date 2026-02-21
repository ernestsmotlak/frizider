<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CookingSessionTimer extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooking_session_id',
        'started_at',
        'duration_seconds',
        'original_duration_seconds',
        'note',
        'status',
        'sort_order',
        'completed_at',
        'paused_at',
        'remaining_seconds_at_pause',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'duration_seconds' => 'integer',
        'original_duration_seconds' => 'integer',
        'remaining_seconds_at_pause' => 'integer',
        'sort_order' => 'integer',
    ];

    public function cookingSession()
    {
        return $this->belongsTo(CookingSession::class);
    }

    protected static function booted()
    {
        static::creating(function ($timer) {
            if (!$timer->status) {
                $timer->status = 'idle';
            }
            if (!$timer->original_duration_seconds) {
                $timer->original_duration_seconds = $timer->duration_seconds;
            }
        });
    }
}
