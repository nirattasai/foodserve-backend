<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


// JWT-Auth
// use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
// use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{

    public function login(Request $request) {

        $user = User::query()->where('username', $request->input('username'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'error' => "Login Fail",
            ]);
        }

        return $this->respondWithToken($user->createToken('api')->plainTextToken, $user);
    }

    public function logout() {
        if (auth()->check()) {
            auth()->logout();
            return true;
        }
        return false;
    }

    public function me() {
        return response()->json([
            'user' => auth()->user(),
        ]);
    }

    public function refresh(Request $request) {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token, $user=null)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => null,
            'user' => $user
        ]);
    }
}
