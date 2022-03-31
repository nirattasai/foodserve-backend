<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


use App\Models\User;
use App\Models\Merchant;


class UserManageController extends Controller
{   
    public function createUser (Request $request) {

        $user = new User();

        $user->username = $request->input('username');
        $user->password = Hash::make($request->input('password'));
        $user->firstname = $request->input('firstName');
        $user->lastname = $request->input('lastName');
        $user->email = $request->input('email');
        $user->telephone_number = $request->input('telephoneNumber');
        $user->id_number = $request->input('idNumber');

        $user->save();

        $merchant = new Merchant();
        $merchant->owner_id = $user->id;
        $merchant->save();

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ]);
    }
}