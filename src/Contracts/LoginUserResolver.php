<?php

namespace Laradeauth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface LoginUserResolver
{
    public function resolve(string $login): ?Authenticatable;
}
