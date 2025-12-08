<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PantryItem extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'user_id',
        'space_id',
        'name',
        'quantity',
        'unit',
        'expiry_date',
        'purchase_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'expiry_date' => 'date',
        'purchase_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spaceStorage()
    {
        return $this->belongsTo(SpaceStorage::class);
    }
}
