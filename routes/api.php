<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth.jwt'])->group(function () {
  Route::post('refreshToken', [AuthController::class, 'refreshToken']);
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('user', [AuthController::class, 'user']);
});

Route::resources([
  'categories' => CategoryController::class,
  'products' => ProductController::class,
  'comments' => CommentController::class,
  'tags' => TagController::class
]);
