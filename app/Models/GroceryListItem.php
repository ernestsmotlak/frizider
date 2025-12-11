<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroceryListItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'grocery_list_id',
        'pantry_item_id',
        'name',
        'quantity',
        'unit',
        'notes',
        'sort_order',
        'is_purchased',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'sort_order' => 'integer',
        'is_purchased' => 'boolean',
    ];

    public function groceryList()
    {
        return $this->belongsTo(GroceryList::class);
    }

    public function pantryItem()
    {
        return $this->belongsTo(PantryItem::class);
    }
    
}
