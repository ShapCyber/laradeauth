<?php

namespace Laradeauth\Contracts;

interface AuthRouteResolver
{
    public function login(): string;

    public function dashboard(): string;

    public function profile(): string;

    public function twoFactorChallenge(): string;

    public function twoFactorCancel(): string;

    public function twoFactorSetup(): string;
}
