<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use Illuminate\Http\Request;

class PantryItemController extends Controller
{
    protected function user()
    {
        return auth('api')->user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PantryItem $pantryItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PantryItem $pantryItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PantryItem $pantryItem)
    {
        //
    }
}
