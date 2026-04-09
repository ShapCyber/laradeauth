<?php

namespace Laradeauth\Contracts;

use Illuminate\Http\Request;

interface MicrosoftIdentityProvider
{
    public function enabled(): bool;

    public function configured(): bool;

    public function authorizationUrl(Request $request): string;

    /**
     * @return array<string, mixed>
     */
    public function resolveIdentity(Request $request): array;
}
