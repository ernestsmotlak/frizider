<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new user and return a JWT.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Because of the 'password' => 'hashed' cast, this will be hashed automatically.
        $user = User::create($data);

        return response()->json([
            'message' => 'User successfully created.',
        ], 201);

        // Use the correct guard: auth('api')
//        $token = auth('api')->login($user);

//        return response()->json([
//            'user' => $user,
//        ])->cookie('token', $token, auth('api')->factory()->getTTL() * 60, '/', null, true, true, false, 'lax');
    }

    /**
     * Login an existing user and return a JWT.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user' => auth('api')->user(),
        ])->cookie('token', $token, auth('api')->factory()->getTTL() * 60, '/', null, true, true, false, 'lax');
    }
}
