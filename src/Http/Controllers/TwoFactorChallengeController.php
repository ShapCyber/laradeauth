<?php

namespace Laradeauth\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Contracts\TwoFactorManager;
use Laradeauth\Contracts\TwoFactorStateResolver;
use Laradeauth\Support\PendingTwoFactorLogin;

class TwoFactorChallengeController extends Controller
{
    public function create(
        Request $request,
        PendingTwoFactorLogin $pendingTwoFactorLogin,
        AuthRouteResolver $authRouteResolver,
    ): View|RedirectResponse {
        if (! $pendingTwoFactorLogin->hasPending($request)) {
            return redirect()->to($authRouteResolver->login())->withErrors([
                'login' => 'Your verification session expired. Please sign in again.',
            ]);
        }

        return view('laradeauth::auth.two-factor-challenge');
    }

    public function store(
        Request $request,
        PendingTwoFactorLogin $pendingTwoFactorLogin,
        TwoFactorManager $twoFactorManager,
        TwoFactorStateResolver $twoFactorStateResolver,
        AuthRouteResolver $authRouteResolver,
    ): RedirectResponse {
        $validated = $request->validate([
            'verification_method' => ['required', 'in:authenticator,backup'],
            'verification_code' => ['nullable', 'string'],
            'backup_code' => ['nullable', 'string'],
        ]);

        $user = $pendingTwoFactorLogin->pendingUser($request);

        if (! $user instanceof Authenticatable || ! $twoFactorStateResolver->hasVerifiedTwoFactor($user)) {
            $pendingTwoFactorLogin->clear($request);

            return redirect()->to($authRouteResolver->login())->withErrors([
                'login' => 'Your verification session expired. Please sign in again.',
            ]);
        }

        if ($validated['verification_method'] === 'backup') {
            if (! $twoFactorManager->consumeBackupCode($user, (string) $validated['backup_code'])) {
                throw ValidationException::withMessages([
                    'backup_code' => 'Backup code is invalid or has already been used.',
                ]);
            }
        } else {
            if (! $twoFactorManager->verifyCodeFor($user, (string) $validated['verification_code'])) {
                throw ValidationException::withMessages([
                    'verification_code' => 'Authenticator code is invalid. Check your app and try again.',
                ]);
            }
        }

        $remember = $pendingTwoFactorLogin->remember($request);
        $destination = $pendingTwoFactorLogin->destination($request);

        $pendingTwoFactorLogin->clear($request);

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        return redirect()->to($destination);
    }

    public function destroy(
        Request $request,
        PendingTwoFactorLogin $pendingTwoFactorLogin,
        AuthRouteResolver $authRouteResolver,
    ): RedirectResponse {
        $pendingTwoFactorLogin->clear($request);
        $request->session()->regenerateToken();

        return redirect()->to($authRouteResolver->login())->with('status', 'Two-factor sign-in cancelled.');
    }
}
