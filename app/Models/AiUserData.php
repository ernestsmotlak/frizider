<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUserData extends Model
{
    protected $table = 'ai_user_data';

    protected $guarded = ['*'];

    protected $casts = [
        'can_use_ai' => 'boolean',
        'credit_balance' => 'integer',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
