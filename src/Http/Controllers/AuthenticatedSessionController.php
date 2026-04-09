<?php

namespace Laradeauth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laradeauth\Contracts\MicrosoftIdentityProvider;
use Laradeauth\Http\Requests\LoginRequest;
use Laradeauth\Support\LoginFlowManager;
use Laradeauth\Support\PendingTwoFactorLogin;

class AuthenticatedSessionController extends Controller
{
    public function create(MicrosoftIdentityProvider $microsoftIdentityProvider): View
    {
        return view('laradeauth::auth.login', [
            'microsoftEnabled' => $microsoftIdentityProvider->enabled() && $microsoftIdentityProvider->configured(),
        ]);
    }

    public function store(LoginRequest $request, LoginFlowManager $loginFlowManager): RedirectResponse
    {
        $user = $request->authenticate();

        return $loginFlowManager->completePasswordLogin($request, $user, $request->boolean('remember'));
    }

    public function destroy(
        Request $request,
        PendingTwoFactorLogin $pendingTwoFactorLogin,
        LoginFlowManager $loginFlowManager,
    ): RedirectResponse {
        $pendingTwoFactorLogin->clear($request);
        $pendingTwoFactorLogin->clearSetupDestination($request);
        $loginFlowManager->clearTrustedProvider($request);
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
