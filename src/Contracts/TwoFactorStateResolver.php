<?php

namespace Laradeauth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface TwoFactorStateResolver
{
    public function hasVerifiedTwoFactor(Authenticatable $user): bool;

    public function isTwoFactorSetupForced(): bool;
}
