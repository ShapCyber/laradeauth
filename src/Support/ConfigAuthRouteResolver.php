<?php

namespace Laradeauth\Support;

use Laradeauth\Contracts\AuthRouteResolver;

class ConfigAuthRouteResolver implements AuthRouteResolver
{
    public function login(): string
    {
        return route((string) config('laradeauth.routes.login', 'login'), absolute: false);
    }

    public function dashboard(): string
    {
        return route((string) config('laradeauth.routes.dashboard', 'dashboard'), absolute: false);
    }

    public function profile(): string
    {
        return route((string) config('laradeauth.routes.profile', 'profile.edit'), absolute: false);
    }

    public function twoFactorChallenge(): string
    {
        return route((string) config('laradeauth.routes.two_factor_challenge', 'two-factor.challenge'), absolute: false);
    }

    public function twoFactorCancel(): string
    {
        return route((string) config('laradeauth.routes.two_factor_cancel', 'two-factor.challenge.cancel'), absolute: false);
    }

    public function twoFactorSetup(): string
    {
        return route((string) config('laradeauth.routes.two_factor_setup', 'two-factor.setup'), absolute: false);
    }
}
