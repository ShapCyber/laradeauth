<?php

namespace Laradeauth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface TwoFactorManager
{
    /**
     * @return array{secret: string, qr_markup: string, otpauth_uri: string}
     */
    public function setupDataFor(Authenticatable $user): array;

    public function verifyCodeFor(Authenticatable $user, string $code): bool;

    public function consumeBackupCode(Authenticatable $user, string $code): bool;

    /**
     * @return array<int, string>
     */
    public function enable(Authenticatable $user): array;

    public function disable(Authenticatable $user): void;

    public function backupCodesRemaining(Authenticatable $user): int;
}
