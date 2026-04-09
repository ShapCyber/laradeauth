# Laradeauth

Reusable Laravel authentication package extracted from the SAMS backend.

## Author

- `Ade Owolabi`

## Current scope

- login request, controllers, routes, and auth views
- authentication contracts for user resolution and route resolution
- Microsoft identity provider contract
- two-factor manager contract
- shared post-login session flow manager
- pending two-factor session state manager with host-app user restoration
- configurable route-name based auth destination resolver
- package service provider with config, routes, and views support

## Installation

Install the package with Composer:

```bash
composer require laradeauth/laradeauth
```

If the host app uses normal Laravel package discovery, the service provider will be registered automatically.

Publish the package config if the host app needs custom route names or layout components:

```bash
php artisan vendor:publish --tag=laradeauth-config
```

Publish the auth views if the host app needs to override the default package templates:

```bash
php artisan vendor:publish --tag=laradeauth-views
```

## Host App Setup

After installation, the host app must bind the application-specific adapters that Laradeauth expects.

Required contracts:

- `Laradeauth\Contracts\LoginUserResolver`
- `Laradeauth\Contracts\PendingTwoFactorUserResolver`
- `Laradeauth\Contracts\TwoFactorStateResolver`
- `Laradeauth\Contracts\MicrosoftUserResolver`
- `Laradeauth\Contracts\MicrosoftIdentityProvider`
- `Laradeauth\Contracts\TwoFactorManager`

Example bindings in a service provider:

```php
use App\Services\Auth\MicrosoftIdentityService;
use App\Services\Auth\TotpService;
use App\Support\Auth\EloquentLoginUserResolver;
use App\Support\Auth\EloquentMicrosoftUserResolver;
use App\Support\Auth\EloquentPendingTwoFactorUserResolver;
use App\Support\Auth\SamsTwoFactorStateResolver;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Laradeauth\Contracts\LoginUserResolver;
use Laradeauth\Contracts\MicrosoftIdentityProvider;
use Laradeauth\Contracts\MicrosoftUserResolver;
use Laradeauth\Contracts\PendingTwoFactorUserResolver;
use Laradeauth\Contracts\TwoFactorManager;
use Laradeauth\Contracts\TwoFactorStateResolver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginUserResolver::class, EloquentLoginUserResolver::class);
        $this->app->singleton(PendingTwoFactorUserResolver::class, EloquentPendingTwoFactorUserResolver::class);
        $this->app->singleton(MicrosoftUserResolver::class, EloquentMicrosoftUserResolver::class);
        $this->app->singleton(TwoFactorStateResolver::class, SamsTwoFactorStateResolver::class);
        $this->app->singleton(MicrosoftIdentityProvider::class, MicrosoftIdentityService::class);
        $this->app->singleton(TwoFactorManager::class, TotpService::class);
    }
}
```

Review the published `config/laradeauth.php` file and adjust these values if needed:

- route names for login, dashboard, profile, and two-factor screens
- guest and security layout component names
- login and pending-user eager-loaded relations

## Host App Responsibilities

The package owns the generic auth flow, but the host application still provides its own adapters for:

- `Laradeauth\Contracts\LoginUserResolver`
- `Laradeauth\Contracts\PendingTwoFactorUserResolver`
- `Laradeauth\Contracts\TwoFactorStateResolver`
- `Laradeauth\Contracts\MicrosoftUserResolver`
- `Laradeauth\Contracts\MicrosoftIdentityProvider`
- `Laradeauth\Contracts\TwoFactorManager`

These bindings let each app decide:

- how local users are found
- how Microsoft identities map to local accounts
- how two-factor state is stored and verified
- which route names and layouts the auth flow should use

## Testing

Package tests run independently with PHPUnit and Orchestra Testbench:

```bash
composer install
vendor\\bin\\phpunit
```

`samsbackend` remains the first consumer and keeps its own feature-level auth regression suite.

## Local Path Installation During Development

If you are developing the package beside another Laravel project, you can install it through a Composer path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laradeauth",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

Then require the package normally:

```bash
composer require laradeauth/laradeauth
```
