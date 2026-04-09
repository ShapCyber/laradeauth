<?php

namespace Laradeauth\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Contracts\TwoFactorManager;
use Laradeauth\Contracts\TwoFactorStateResolver;
use Laradeauth\Support\PendingTwoFactorLogin;

class TwoFactorSetupController extends Controller
{
    public function create(
        Request $request,
        TwoFactorManager $twoFactorManager,
        TwoFactorStateResolver $twoFactorStateResolver,
        PendingTwoFactorLogin $pendingTwoFactorLogin,
        AuthRouteResolver $authRouteResolver,
    ): View {
        $user = $request->user();
        $backupCodes = (array) session('backup_codes', []);
        $isVerified = $user instanceof Authenticatable
            ? $twoFactorStateResolver->hasVerifiedTwoFactor($user)
            : false;
        $setupData = null;

        if ($user instanceof Authenticatable && ! $isVerified) {
            $setupData = $twoFactorManager->setupDataFor($user);
        }

        return view('laradeauth::auth.two-factor-setup', [
            'backupCodes' => $backupCodes,
            'backupCodesRemaining' => $user instanceof Authenticatable ? $twoFactorManager->backupCodesRemaining($user) : 0,
            'continueTo' => session('totp_redirect_to', $pendingTwoFactorLogin->setupDestination($request)),
            'dashboardRoute' => $authRouteResolver->dashboard(),
            'isTwoFactorForced' => $twoFactorStateResolver->isTwoFactorSetupForced(),
            'profileRoute' => $authRouteResolver->profile(),
            'setupData' => $setupData,
            'twoFactorVerified' => $isVerified,
        ]);
    }

    public function store(
        Request $request,
        TwoFactorManager $twoFactorManager,
        TwoFactorStateResolver $twoFactorStateResolver,
        PendingTwoFactorLogin $pendingTwoFactorLogin,
        AuthRouteResolver $authRouteResolver,
    ): RedirectResponse {
        $validated = $request->validate([
            'verification_code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user instanceof Authenticatable) {
            return redirect()->to($authRouteResolver->login());
        }

        if ($twoFactorStateResolver->hasVerifiedTwoFactor($user)) {
            return redirect()->to($authRouteResolver->twoFactorSetup());
        }

        $twoFactorManager->setupDataFor($user);

        if (! $twoFactorManager->verifyCodeFor($user, (string) $validated['verification_code'])) {
            return back()
                ->withErrors([
                    'verification_code' => 'Authenticator code is invalid. Check your app and try again.',
                ])
                ->withInput();
        }

        $backupCodes = $twoFactorManager->enable($user);
        $redirectTo = $pendingTwoFactorLogin->setupDestination($request);

        $pendingTwoFactorLogin->clearSetupDestination($request);

        return redirect()
            ->to($authRouteResolver->twoFactorSetup())
            ->with('backup_codes', $backupCodes)
            ->with('status', 'totp-enabled')
            ->with('totp_redirect_to', $redirectTo);
    }

    public function destroy(
        Request $request,
        TwoFactorManager $twoFactorManager,
        TwoFactorStateResolver $twoFactorStateResolver,
        PendingTwoFactorLogin $pendingTwoFactorLogin,
        AuthRouteResolver $authRouteResolver,
    ): RedirectResponse {
        $user = $request->user();

        if ($user instanceof Authenticatable) {
            $twoFactorManager->disable($user);
        }

        if ($twoFactorStateResolver->isTwoFactorSetupForced()) {
            $pendingTwoFactorLogin->rememberSetupDestination($request, $authRouteResolver->profile());

            return redirect()
                ->to($authRouteResolver->twoFactorSetup())
                ->with('status', 'totp-disabled');
        }

        return redirect()
            ->to($authRouteResolver->profile())
            ->with('status', 'totp-disabled');
    }
}
