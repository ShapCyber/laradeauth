<?php

namespace Laradeauth\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Contracts\PendingTwoFactorUserResolver;

/**
 * Stores and restores the temporary session state used during two-factor login.
 *
 * This class deliberately avoids direct model knowledge. The host application
 * is responsible for resolving the pending authenticatable by identifier.
 */
class PendingTwoFactorLogin
{
    private const PENDING_USER_ID = 'auth.pending_totp_user_id';
    private const PENDING_REMEMBER = 'auth.pending_totp_remember';
    private const PENDING_DESTINATION = 'auth.pending_totp_destination';
    private const SETUP_DESTINATION = 'auth.totp_setup_destination';

    public function __construct(
        private readonly AuthRouteResolver $authRouteResolver,
        private readonly PendingTwoFactorUserResolver $pendingTwoFactorUserResolver,
    ) {
    }

    public function startChallenge(Request $request, Authenticatable $user, bool $remember): void
    {
        $request->session()->put([
            self::PENDING_USER_ID => $user->getAuthIdentifier(),
            self::PENDING_REMEMBER => $remember,
            self::PENDING_DESTINATION => $this->sanitizeDestination($request->session()->get('url.intended')),
        ]);
    }

    public function hasPending(Request $request): bool
    {
        return filled($request->session()->get(self::PENDING_USER_ID));
    }

    public function pendingUser(Request $request): ?Authenticatable
    {
        $userId = $request->session()->get(self::PENDING_USER_ID);

        if (! $userId) {
            return null;
        }

        return $this->pendingTwoFactorUserResolver->resolve($userId);
    }

    public function remember(Request $request): bool
    {
        return (bool) $request->session()->get(self::PENDING_REMEMBER, false);
    }

    public function destination(Request $request): string
    {
        return $this->sanitizeDestination($request->session()->get(self::PENDING_DESTINATION));
    }

    public function rememberSetupDestination(Request $request, mixed $destination = null): void
    {
        $request->session()->put(
            self::SETUP_DESTINATION,
            $this->sanitizeDestination($destination ?? $request->getRequestUri())
        );
    }

    public function setupDestination(Request $request): string
    {
        return $this->sanitizeDestination($request->session()->get(self::SETUP_DESTINATION));
    }

    public function clear(Request $request): void
    {
        $request->session()->forget([
            self::PENDING_USER_ID,
            self::PENDING_REMEMBER,
            self::PENDING_DESTINATION,
        ]);
    }

    public function clearSetupDestination(Request $request): void
    {
        $request->session()->forget(self::SETUP_DESTINATION);
    }

    public function sanitizeDestination(mixed $destination): string
    {
        $destination = trim((string) $destination);

        if ($destination === '' || Str::startsWith($destination, ['http://', 'https://', '//'])) {
            return $this->authRouteResolver->dashboard();
        }

        if (! Str::startsWith($destination, ['/', '?', '#'])) {
            $destination = '/'.ltrim($destination, '/');
        }

        foreach ($this->blockedDestinations() as $blockedDestination) {
            if ($destination === $blockedDestination || Str::startsWith($destination, $blockedDestination.'?')) {
                return $this->authRouteResolver->dashboard();
            }
        }

        return $destination;
    }

    /**
     * @return array<int, string>
     */
    private function blockedDestinations(): array
    {
        return array_values(array_filter([
            parse_url($this->authRouteResolver->login(), PHP_URL_PATH),
            parse_url($this->authRouteResolver->twoFactorChallenge(), PHP_URL_PATH),
            parse_url($this->authRouteResolver->twoFactorCancel(), PHP_URL_PATH),
            parse_url($this->authRouteResolver->twoFactorSetup(), PHP_URL_PATH),
        ], static fn (?string $path): bool => filled($path)));
    }
}
