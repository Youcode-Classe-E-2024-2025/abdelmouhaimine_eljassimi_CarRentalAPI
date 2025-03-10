<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request) {
         $attributes = $request->validate([
             'name' => 'required',
             'email' => 'required|email|unique:users',
             'password' => 'required|confirmed',
         ]);
         $user  = User::create($attributes);

        $token = $user->createToken($request->name);

         return ['user'=>$user,'token'=>$token->plainTextToken];
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(["message" => "Les informations d'identification sont incorrectes"], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request) {
         $request->user()->tokens()->delete();
         return ['message'=>'You have been logged out'];
    }
}
