<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Return the currently authenticated user.
     */
    public function me()
    {
        return response()->json([
            'data' => auth('api')->user(),
        ]);
    }

    /**
     * Update the currently authenticated user.
     */
    public function updateMe(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Delete the currently authenticated user.
     */
    public function destroyMe()
    {
        $user = auth('api')->user();

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
