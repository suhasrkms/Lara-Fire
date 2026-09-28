<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Kreait\Firebase\Auth\UserRecord;

/**
 * Lightweight, serializable Authenticatable backed by a Firebase Auth user record.
 */
final class FirebaseUser implements Arrayable, Authenticatable, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $customClaims
     * @param  list<string>  $providers
     */
    public function __construct(
        public readonly string $uid,
        public readonly ?string $email = null,
        public readonly ?string $displayName = null,
        public readonly ?string $photoUrl = null,
        public readonly bool $emailVerified = false,
        public readonly bool $disabled = false,
        public readonly array $customClaims = [],
        public readonly array $providers = [],
        public readonly ?string $createdAt = null,
        public readonly ?string $lastLoginAt = null,
    ) {}

    public static function fromRecord(UserRecord $record): self
    {
        $providers = [];
        foreach ($record->providerData as $info) {
            $providers[] = $info->providerId;
        }

        return new self(
            uid: $record->uid,
            email: $record->email,
            displayName: $record->displayName,
            photoUrl: $record->photoUrl,
            emailVerified: $record->emailVerified,
            disabled: $record->disabled,
            customClaims: $record->customClaims,
            providers: $providers,
            createdAt: $record->metadata->createdAt->format(DATE_ATOM),
            lastLoginAt: $record->metadata->lastLoginAt?->format(DATE_ATOM),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uid: (string) $data['uid'],
            email: $data['email'] ?? null,
            displayName: $data['displayName'] ?? null,
            photoUrl: $data['photoUrl'] ?? null,
            emailVerified: (bool) ($data['emailVerified'] ?? false),
            disabled: (bool) ($data['disabled'] ?? false),
            customClaims: (array) ($data['customClaims'] ?? []),
            providers: array_values((array) ($data['providers'] ?? [])),
            createdAt: $data['createdAt'] ?? null,
            lastLoginAt: $data['lastLoginAt'] ?? null,
        );
    }

    public function isAdmin(): bool
    {
        return ($this->customClaims['admin'] ?? false) === true;
    }

    /**
     * Social-only accounts (Google, GitHub…) are verified by the provider.
     */
    public function hasPasswordProvider(): bool
    {
        // No providers (e.g. anonymous) is treated like password: must verify.
        return $this->providers === [] || in_array('password', $this->providers, true);
    }

    public function name(): string
    {
        return $this->displayName ?: (string) strstr((string) $this->email, '@', true) ?: 'User';
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name())) ?: [];

        return strtoupper(implode('', array_map(fn ($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2))));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'uid' => $this->uid,
            'email' => $this->email,
            'displayName' => $this->displayName,
            'photoUrl' => $this->photoUrl,
            'emailVerified' => $this->emailVerified,
            'disabled' => $this->disabled,
            'customClaims' => $this->customClaims,
            'providers' => $this->providers,
            'createdAt' => $this->createdAt,
            'lastLoginAt' => $this->lastLoginAt,
        ];
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'uid' => $this->uid,
            'email' => $this->email,
            'displayName' => $this->displayName,
            'photoUrl' => $this->photoUrl,
            'emailVerified' => $this->emailVerified,
            'isAdmin' => $this->isAdmin(),
            'providers' => $this->providers,
            'createdAt' => $this->createdAt,
            'lastLoginAt' => $this->lastLoginAt,
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'uid';
    }

    public function getAuthIdentifier(): string
    {
        return $this->uid;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // Remember-me is handled by Firebase sessions, not Laravel tokens.
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
