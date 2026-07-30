<?php

namespace App\Http\Middleware;

use App\Auth\FirebaseUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof FirebaseUser || ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            return redirect()->route('home')->with('error', 'You need admin access for that page.');
        }

        return $next($request);
    }
}
