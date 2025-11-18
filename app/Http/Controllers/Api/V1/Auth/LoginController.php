<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email','password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        auth('api')->setToken($token);
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        $user = auth('api')->user();

        return response()->json([
            'access_token'  => $token,
            'refresh_token' => JWTAuth::claims(['type' => 'refresh'])->fromUser($user),  // Generate refresh token properly
            'token_type'    => 'bearer',
            'expires_in'    => JWTAuth::factory()->getTTL() * 60  // Use JWTAuth facade for TTL
        ]);
    }
}
