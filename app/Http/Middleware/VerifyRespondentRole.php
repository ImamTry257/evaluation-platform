<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyRespondentRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'respondent') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Respondent access required.',
            ], 403);
        }

        return $next($request);
    }
}
