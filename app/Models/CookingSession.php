<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipe_id',
        'timer_fab_x_percent',
        'timer_fab_y_percent'
    ];

    protected $casts = [
        'timer_fab_x_percent' => 'float',
        'timer_fab_y_percent' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function cookingSessionTimers()
    {
        return $this->hasMany(CookingSessionTimer::class);
    }
}
