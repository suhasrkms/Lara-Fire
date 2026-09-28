<?php

namespace App\Http\Middleware;

use App\Auth\FirebaseUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Social-only accounts (Google, GitHub…) are trusted via their provider.
        if ($user instanceof FirebaseUser && ! $user->emailVerified && $user->hasPasswordProvider()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your email address is not verified.'], 403)
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
