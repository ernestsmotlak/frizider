<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShoppingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'grocery_list_ids',
    ];

    protected $casts = [
        'grocery_list_ids' => 'array',
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }

    public function shoppingItems()
    {
        return $this->hasMany(ShoppingItem::class);
    }

}
