@php($securityLayoutComponent = config('laradeauth.views.security_layout_component', 'security-layout'))

<x-dynamic-component :component="$securityLayoutComponent">
    <x-slot name="header">
        <div>
            <p class="section-kicker mb-2">Account Security</p>
            <h1 class="display-6 mb-2">Set up two-factor authentication</h1>
            <p class="mb-0 text-secondary-emphasis">
                Complete this security step before entering the main SAMS workspace.
            </p>
            @if ($isTwoFactorForced && ! $twoFactorVerified)
                <div class="alert alert-warning alert-dismissible fade show border-0 mt-3 mb-0" role="alert">
                    Two-factor authentication is required for your account before you can continue using SAMS.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('status') === 'totp-disabled')
                <div class="alert alert-warning alert-dismissible fade show border-0 mt-3 mb-0" role="alert">
                    Two-factor authentication was removed. Set up your authenticator again to keep your account protected.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="row g-4">
        @if (count($backupCodes))
            <div class="col-12">
                <div class="card sams-panel border-0 h-100">
                    <div class="card-body p-4 p-lg-5">
                        <p class="section-kicker mb-2">Recovery Codes</p>
                        <h2 class="h3 mb-3">Save these backup codes now</h2>
                        <p class="text-secondary mb-4">
                            Each code can be used once if you lose access to your authenticator app. They are only shown this time.
                        </p>

                        <div class="backup-code-grid mb-4">
                            @foreach ($backupCodes as $backupCode)
                                <div class="backup-code-tile">{{ $backupCode }}</div>
                            @endforeach
                        </div>

                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ $continueTo ?: $dashboardRoute }}" class="btn btn-dark px-4">
                                Continue to SAMS
                            </a>

                            <a href="{{ $profileRoute }}" class="btn btn-outline-secondary px-4">
                                Open profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($twoFactorVerified)
            <div class="col-12 col-xl-8">
                <div class="card sams-panel border-0 h-100">
                    <div class="card-body p-4 p-lg-5">
                        <p class="section-kicker mb-2">Status</p>
                        <h2 class="h3 mb-3">Two-factor authentication is active</h2>
                        <p class="text-secondary mb-4">
                            Your account now requires an authenticator code during sign-in. You currently have
                            <strong>{{ $backupCodesRemaining }}</strong> backup codes remaining.
                        </p>

                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ $dashboardRoute }}" class="btn btn-dark px-4">
                                Return to dashboard
                            </a>

                            <a href="{{ $profileRoute }}" class="btn btn-outline-secondary px-4">
                                Manage in profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-12 col-xl-7">
                <div class="card sams-panel border-0 h-100">
                    <div class="card-body p-4 p-lg-5">
                        <p class="section-kicker mb-2">Authenticator Setup</p>
                        <h2 class="h3 mb-3">Scan the code with your app</h2>
                        <p class="text-secondary mb-4">
                            Use Google Authenticator, Microsoft Authenticator, Authy, 1Password, or another compatible app.
                        </p>

                        <div class="totp-qr-shell mb-4">
                            <div class="totp-qr-code" role="img" aria-label="Authenticator QR code">
                                {!! $setupData['qr_markup'] !!}
                            </div>
                            <p class="fw-semibold mb-2">Manual entry key</p>
                            <code class="totp-secret-key">{{ trim(chunk_split($setupData['secret'], 4, ' ')) }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card sams-panel border-0 h-100">
                    <div class="card-body p-4 p-lg-5">
                        <p class="section-kicker mb-2">Confirm Setup</p>
                        <h2 class="h3 mb-3">Verify your first code</h2>
                        <p class="text-secondary mb-4">
                            Enter the current 6-digit code from your authenticator app to finish setup.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('two-factor.setup.store') }}" class="vstack gap-3">
                            @csrf

                            <div>
                                <label for="verification_code" class="form-label">Authenticator code</label>
                                @include('laradeauth::auth.partials.otp-input', [
                                    'id' => 'verification_code',
                                    'name' => 'verification_code',
                                    'value' => old('verification_code'),
                                    'autoSubmit' => true,
                                    'required' => true,
                                ])
                            </div>

                            <div class="d-flex flex-wrap gap-3 pt-2">
                                <button type="submit" class="btn btn-dark px-4">
                                    <i class="bi bi-shield-lock me-2"></i>Enable two-factor authentication
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary px-4">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
