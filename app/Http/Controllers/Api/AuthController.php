<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email'=> 'required|email|unique:users,email',
                'password'=>'required|string|confirmed|min:6',
                'phone'=>'nullable|string|max:20',
            ]);    
        } catch (ValidationException $e){
            return response()->json([
                'message'=>'Failed to register user',
                'errors'=>$e->errors()
            ], 422);
        }

        $validated['password']=Hash::make($validated['password']);
        $validated['remember_token']=Str::random(10);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user'=> [
                'id' => $user->id,
                'name'=> $user->name,
                'email'=> $user->email,
                'phone'=>$user->phone,

            ]
        ], 201);
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user= User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message' =>'Invalid email or password'], 401);
        }

        $token=$user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_admin' => $user->is_admin,
            ],
            'access' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request){
        $user = $request->user();
        if ($user){
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logout successful']);
    }
}
