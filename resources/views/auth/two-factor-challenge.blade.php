@php($guestLayoutComponent = config('laradeauth.views.guest_layout_component', 'guest-layout'))

<x-dynamic-component :component="$guestLayoutComponent">
    <div class="mb-4">
        <p class="section-kicker mb-2">Two-Factor Verification</p>
        <h2 class="h1 mb-2">Confirm your sign-in</h2>
        <p class="text-secondary mb-0">
            Enter the current code from your authenticator app to complete access to SAMS, or use one of your backup codes.
        </p>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="vstack gap-4">
        @csrf

        <div class="panel-subtle">
            <div class="form-check mb-3">
                <input
                    class="form-check-input"
                    type="radio"
                    name="verification_method"
                    id="verification_method_authenticator"
                    value="authenticator"
                    @checked(old('verification_method', 'authenticator') === 'authenticator')
                >
                <label class="form-check-label fw-semibold" for="verification_method_authenticator">
                    Use authenticator code
                </label>
            </div>

            <label for="verification_code" class="form-label">Authenticator code</label>
            @include('laradeauth::auth.partials.otp-input', [
                'id' => 'verification_code',
                'name' => 'verification_code',
                'value' => old('verification_code'),
                'autoSubmit' => true,
                'methodField' => 'verification_method',
                'methodValue' => 'authenticator',
            ])
            <div class="form-hint mt-2">Enter the 6-digit code shown in your authenticator app.</div>
        </div>

        <div class="panel-subtle">
            <div class="form-check mb-3">
                <input
                    class="form-check-input"
                    type="radio"
                    name="verification_method"
                    id="verification_method_backup"
                    value="backup"
                    @checked(old('verification_method') === 'backup')
                >
                <label class="form-check-label fw-semibold" for="verification_method_backup">
                    Use backup code
                </label>
            </div>

            <label for="backup_code" class="form-label">Backup code</label>
            <input
                id="backup_code"
                class="form-control form-control-lg"
                type="text"
                name="backup_code"
                value="{{ old('backup_code') }}"
                autocomplete="off"
                placeholder="ABCD-EFGH"
            >
            <div class="form-hint mt-2">Backup codes can only be used once.</div>
        </div>

        <div class="d-flex flex-wrap gap-3">
            <button type="submit" class="btn btn-dark btn-lg px-4">
                <i class="bi bi-shield-check me-2"></i>Verify and continue
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('two-factor.challenge.cancel') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-outline-secondary">
            Cancel sign-in
        </button>
    </form>
</x-dynamic-component>
