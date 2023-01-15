<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
  public function handle(Request $request, Closure $next, $role)
  {
    if (!$request->user()->isAdmin()) {
      return response()->json(['error' => "You are must be $role"], 403);
    }
    return $next($request);
  }
}