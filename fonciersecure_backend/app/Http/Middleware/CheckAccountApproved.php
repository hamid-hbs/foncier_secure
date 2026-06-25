<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        if (!$request->user()->is_active) {
            return response()->json([
                'message' => 'Votre compte est en attente de validation par un administrateur.',
                'code' => 'ACCOUNT_PENDING_APPROVAL',
            ], 403);
        }

        return $next($request);
    }
}
