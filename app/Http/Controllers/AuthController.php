<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $credentials = $request->only(['email', 'password']);
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email|max:255',
      'password' => 'required|string|min:6',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $token = JWTAuth::attempt($credentials);
      $user = User::where('email', $request->email)->first();
      if (!$token || !$user) {
        return response()->json(['error' => 'Invalid credentials'], 400);
      }
    } catch (JWTException $e) {
      return response()->json(['error' => 'Could not create token'], 500);
    }
    $user = new UserResource($user);
    return response()->json(compact('token', 'user'));
  }

  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:6|confirmed',
      'phone' => 'nullable'
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $user = User::create([
        'name' => $request->get('name'),
        'email' => $request->get('email'),
        'password' => Hash::make($request->get('password')),
        'phone' => $request->get('phone')
      ]);
      $user->roles()->attach([3]);
      $token = JWTAuth::fromUser($user);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    $user = new UserResource($user);
    return response()->json(compact('token', 'user'), 201);
  }

  public function logout()
  {
    try {
      JWTAuth::invalidate();
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json([
      'message' => 'Successfully logged out',
    ]);
  }

  public function user(Request $request)
  {
    return new UserResource($request->user());
  }

  public function refreshToken()
  {
    try {
      $refreshToken = JWTAuth::refresh();
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(compact('refreshToken'));
  }
}
