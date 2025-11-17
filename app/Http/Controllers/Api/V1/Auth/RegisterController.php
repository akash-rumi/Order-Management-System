<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class RegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'role'     => 'required|in:customer,vendor,admin',
                'phone'    => 'required|string|regex:/^01[3-9]\d{8}$/|max:11',
            ]);
        } catch (ValidationException $e) {
            // Return exact validation errors with 422 status
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'] ?? 'customer',
            'phone'    => $data['phone'],
        ]);

        if (!$user) {
            return response()->json(['message' => 'Registration failed'], 500);
        }

        return response()->json([
            'message' => 'Registration successful',
            'user'    => $user
        ], 201);
    }
}
