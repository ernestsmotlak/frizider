<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'shopping_session_id',
        'grocery_list_item_id',
        'name',
        'quantity',
        'notes',
        'unit',
        'sort_order',
        'is_purchased',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_purchased' => 'boolean',
        'quantity' => 'decimal:2',
    ];

    public function shoppingSession()
    {
        return $this->belongsTo(ShoppingSession::class);
    }

    public function groceryListItem()
    {
        return $this->belongsTo(GroceryListItem::class);
    }
}
