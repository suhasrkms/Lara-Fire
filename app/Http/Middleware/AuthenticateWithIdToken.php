<?php

namespace App\Http\Middleware;

use App\Auth\FirebaseUserProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Stateless API auth: `Authorization: Bearer <Firebase ID token>`.
 */
class AuthenticateWithIdToken
{
    public function __construct(protected FirebaseUserProvider $users) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthorized('Missing bearer token.');
        }

        try {
            $verified = app(FirebaseAuth::class)->verifyIdToken($token, checkIfRevoked: true);
        } catch (Throwable) {
            return $this->unauthorized('Invalid or expired ID token.');
        }

        $uid = (string) $verified->claims()->get('sub');
        $user = $uid !== '' ? $this->users->retrieveById($uid) : null;

        if (! $user) {
            return $this->unauthorized('User not found or disabled.');
        }

        if (! $user->emailVerified && $user->hasPasswordProvider()) {
            return response()->json(['message' => 'Your email address is not verified.'], 403);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    protected function unauthorized(string $message): Response
    {
        return response()->json(['message' => $message], 401);
    }
}
