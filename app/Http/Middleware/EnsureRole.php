<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Usage: ->middleware('role:admin') or ->middleware('role:it,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $actual = strtolower((string) ($user->role ?? 'user'));
        $allowed = array_map(
            static fn (string $r) => strtolower(trim($r)),
            $roles
        );

        if (!in_array($actual, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
