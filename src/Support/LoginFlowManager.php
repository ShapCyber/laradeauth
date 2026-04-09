<?php

namespace Laradeauth\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Contracts\TwoFactorStateResolver;

/**
 * Coordinates the shared post-authentication session flow.
 *
 * Host applications provide the second-factor policy and state checks, while
 * this class owns the generic session, redirect, and temporary challenge flow.
 */
class LoginFlowManager
{
    private const TRUSTED_PROVIDER_SESSION_KEY = 'laradeauth.trusted_provider_authenticated';

    public function __construct(
        private readonly PendingTwoFactorLogin $pendingTwoFactorLogin,
        private readonly AuthRouteResolver $authRouteResolver,
        private readonly TwoFactorStateResolver $twoFactorStateResolver,
    ) {
    }

    public function completePasswordLogin(
        Request $request,
        Authenticatable $user,
        bool $remember = false,
    ): RedirectResponse {
        $this->clearTrustedProvider($request);

        if ($this->twoFactorStateResolver->hasVerifiedTwoFactor($user)) {
            $this->pendingTwoFactorLogin->startChallenge($request, $user, $remember);

            return redirect()->to($this->authRouteResolver->twoFactorChallenge());
        }

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        if ($this->twoFactorStateResolver->isTwoFactorSetupForced()) {
            $this->pendingTwoFactorLogin->rememberSetupDestination($request, $request->session()->get('url.intended'));

            return redirect()->to($this->authRouteResolver->twoFactorSetup());
        }

        return redirect()->intended($this->authRouteResolver->dashboard());
    }

    public function completeWithTrustedProvider(Request $request, Authenticatable $user): RedirectResponse
    {
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put(self::TRUSTED_PROVIDER_SESSION_KEY, true);

        return redirect()->intended($this->authRouteResolver->dashboard());
    }

    public function hasTrustedProvider(Request $request): bool
    {
        return (bool) $request->session()->get(self::TRUSTED_PROVIDER_SESSION_KEY, false);
    }

    public function clearTrustedProvider(Request $request): void
    {
        $request->session()->forget(self::TRUSTED_PROVIDER_SESSION_KEY);
    }
}
