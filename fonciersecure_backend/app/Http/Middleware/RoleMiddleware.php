<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $userRole = $request->user()->role?->nom;

        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Accès refusé. Rôle requis : ' . implode(', ', $roles),
                'roles_requis' => $roles,
                'votre_role' => $userRole,
            ], 403);
        }

        return $next($request);
    }
}
