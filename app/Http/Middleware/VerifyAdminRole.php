<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Admin access required.',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}
