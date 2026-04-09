@php($guestLayoutComponent = config('laradeauth.views.guest_layout_component', 'guest-layout'))

<x-dynamic-component :component="$guestLayoutComponent">
    <div class="mb-4">
        <p class="section-kicker mb-2">Secure Sign In</p>
        <h2 class="h1 mb-2">Access the SAMS dashboard</h2>
        <p class="text-secondary mb-0">
            Sign in with your HR or line-manager account to view live attendance, export logs, and manage staff access. You can use your SAMS password or Microsoft account, and if two-factor authentication is enabled you will confirm the sign-in with your authenticator app next.
        </p>
    </div>

    @if (request()->query('setup') === 'complete')
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4" role="alert">
            System configuration has been saved. Log in with the system administrator account you created during setup.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="vstack gap-3">
        @csrf

        <div>
            <label for="login" class="form-label">Email address or username</label>
            <input id="login" class="form-control form-control-lg" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username">
        </div>

        <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="password" class="form-label mb-0">Password</label>

                @if (Route::has('password.request'))
                    <a class="small text-decoration-none" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <input id="password" class="form-control form-control-lg" type="password" name="password" required autocomplete="current-password">
        </div>

        <div class="form-check">
            <input id="remember_me" class="form-check-input" type="checkbox" name="remember">
            <label class="form-check-label" for="remember_me">
                Keep me signed in on this device
            </label>
        </div>

        <button type="submit" class="btn btn-dark btn-lg rounded-pill px-4 align-self-start">
            <i class="bi bi-box-arrow-in-right me-2"></i>Log in
        </button>
    </form>

    @if ($microsoftEnabled)
        <div class="my-4 text-center text-secondary small text-uppercase">or continue with</div>

        <a href="{{ route('login.microsoft.redirect') }}" class="btn btn-outline-dark btn-lg px-4">
            <i class="bi bi-microsoft me-2"></i>Sign in with Microsoft
        </a>
    @endif
</x-dynamic-component>
