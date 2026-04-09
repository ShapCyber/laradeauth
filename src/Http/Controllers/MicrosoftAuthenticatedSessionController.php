<?php

namespace Laradeauth\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Contracts\MicrosoftIdentityProvider;
use Laradeauth\Contracts\MicrosoftUserResolver;
use Laradeauth\Exceptions\MicrosoftAuthenticationException;
use Laradeauth\Support\LoginFlowManager;

class MicrosoftAuthenticatedSessionController extends Controller
{
    public function redirect(
        Request $request,
        MicrosoftIdentityProvider $microsoftIdentityProvider,
        AuthRouteResolver $authRouteResolver,
    ): RedirectResponse {
        try {
            return redirect()->away($microsoftIdentityProvider->authorizationUrl($request));
        } catch (MicrosoftAuthenticationException $exception) {
            return redirect()->to($authRouteResolver->login())->withErrors([
                'login' => $exception->getMessage(),
            ]);
        }
    }

    public function callback(
        Request $request,
        MicrosoftIdentityProvider $microsoftIdentityProvider,
        LoginFlowManager $loginFlowManager,
        MicrosoftUserResolver $microsoftUserResolver,
        AuthRouteResolver $authRouteResolver,
    ): RedirectResponse {
        try {
            $identity = $microsoftIdentityProvider->resolveIdentity($request);
        } catch (MicrosoftAuthenticationException $exception) {
            return redirect()->to($authRouteResolver->login())->withErrors([
                'login' => $exception->getMessage(),
            ]);
        }

        $user = $microsoftUserResolver->resolve($identity);

        if (! $user instanceof Authenticatable) {
            return redirect()->to($authRouteResolver->login())->withErrors([
                'login' => sprintf(
                    'No SAMS account is registered for %s. Contact the system administrator.',
                    strtolower((string) ($identity['email'] ?? ''))
                ),
            ]);
        }

        return $loginFlowManager->completeWithTrustedProvider($request, $user);
    }
}
