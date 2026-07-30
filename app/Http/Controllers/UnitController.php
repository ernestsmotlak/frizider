<?php

namespace App\Http\Controllers;

use App\Enums\Unit;

class UnitController extends Controller
{
    /**
     * The allowed unit values — the frontend feeds its unit dropdowns from this.
     */
    public function index()
    {
        return response()->json([
            'data' => Unit::values(),
        ]);
    }
}
