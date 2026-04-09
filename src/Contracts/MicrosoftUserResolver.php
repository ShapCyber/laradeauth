<?php

namespace Laradeauth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface MicrosoftUserResolver
{
    /**
     * @param  array<string, mixed>  $identity
     */
    public function resolve(array $identity): ?Authenticatable;
}
