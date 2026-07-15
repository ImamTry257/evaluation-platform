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
                'status' => false,
                'message' => 'Unauthorized. Respondent access required.',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}
