<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;

class RefreshController extends Controller
{
    public function refresh()
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
