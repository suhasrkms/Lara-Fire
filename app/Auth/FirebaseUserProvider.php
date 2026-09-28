<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Container\Container;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\UserNotFound;

/**
 * Resolves the session user from Firebase Auth (cached for a short TTL).
 */
class FirebaseUserProvider implements UserProvider
{
    public function __construct(
        protected Container $container,
        protected Cache $cache,
        protected int $ttl = 60,
    ) {}

    /**
     * Resolved lazily so guest pages work before credentials are configured.
     */
    protected function auth(): FirebaseAuth
    {
        return $this->container->make(FirebaseAuth::class);
    }

    public static function cacheKey(string $uid): string
    {
        return 'larafire.user.'.$uid;
    }

    public function forget(string $uid): void
    {
        $this->cache->forget(self::cacheKey($uid));
    }

    public function fresh(string $uid): ?FirebaseUser
    {
        $this->forget($uid);

        return $this->retrieveById($uid);
    }

    public function retrieveById($identifier): ?FirebaseUser
    {
        if (! is_string($identifier) || $identifier === '') {
            return null;
        }

        $data = $this->cache->remember(self::cacheKey($identifier), $this->ttl, function () use ($identifier) {
            try {
                return FirebaseUser::fromRecord($this->auth()->getUser($identifier))->toArray();
            } catch (UserNotFound) {
                return null;
            }
        });

        if ($data === null) {
            $this->forget($identifier);

            return null;
        }

        $user = FirebaseUser::fromArray($data);

        return $user->disabled ? null : $user;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void {}

    /**
     * Credentials: ['email' => ..., 'password' => ...]. Firebase validates them.
     */
    public function retrieveByCredentials(array $credentials): ?FirebaseUser
    {
        try {
            $result = $this->auth()->signInWithEmailAndPassword(
                (string) ($credentials['email'] ?? ''),
                (string) ($credentials['password'] ?? ''),
            );
        } catch (FailedToSignIn) {
            // Wrong email/password. Connection errors bubble up to the controller.
            return null;
        }

        $uid = $result->firebaseUserId();

        return $uid ? $this->fresh($uid) : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // retrieveByCredentials() only returns a user after Firebase accepted the password.
        return $user instanceof FirebaseUser;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}
}
