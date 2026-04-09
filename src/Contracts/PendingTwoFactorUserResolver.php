<?php

namespace Laradeauth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface PendingTwoFactorUserResolver
{
    public function resolve(int|string $identifier): ?Authenticatable;
}
