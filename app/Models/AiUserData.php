<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUserData extends Model
{
    protected $table = 'ai_user_data';

    protected $guarded = ['*'];

    protected $casts = [
        'can_generate_ai_calls' => 'boolean',
        'ai_calls_remaining' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
