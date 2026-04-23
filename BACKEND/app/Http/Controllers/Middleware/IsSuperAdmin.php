<?php
// app/Http/Middleware/IsSuperAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Forbidden. Super admins only.'], 403);
        }
        return $next($request);
    }
}