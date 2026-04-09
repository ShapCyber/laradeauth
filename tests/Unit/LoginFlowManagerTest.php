<?php

namespace Laradeauth\Tests\Unit;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Contracts\PendingTwoFactorUserResolver;
use Laradeauth\Contracts\TwoFactorStateResolver;
use Laradeauth\Support\LoginFlowManager;
use Laradeauth\Support\PendingTwoFactorLogin;
use Laradeauth\Tests\TestCase;

class LoginFlowManagerTest extends TestCase
{
    public function test_password_login_starts_a_pending_challenge_for_verified_users(): void
    {
        $user = new GenericUser(['id' => 42, 'email' => 'verified@example.com']);
        $pending = $this->pendingTwoFactorLogin([42 => $user]);
        $manager = new LoginFlowManager($pending, $this->routeResolver(), $this->twoFactorStateResolver(true, false));
        $request = $this->makeRequestWithSession();

        $response = $manager->completePasswordLogin($request, $user, true);

        $this->assertStringEndsWith('/login/two-factor', $response->getTargetUrl());
        $this->assertTrue($pending->hasPending($request));
        $this->assertFalse($manager->hasTrustedProvider($request));
    }

    public function test_password_login_redirects_to_setup_when_two_factor_is_forced(): void
    {
        $user = new GenericUser(['id' => 7, 'email' => 'person@example.com']);
        $pending = $this->pendingTwoFactorLogin();
        $manager = new LoginFlowManager($pending, $this->routeResolver(), $this->twoFactorStateResolver(false, true));
        $request = $this->makeRequestWithSession();
        $request->session()->put('url.intended', '/reports');
        $guard = Mockery::mock();
        $guard->shouldReceive('login')->once()->with($user, false);
        Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

        $response = $manager->completePasswordLogin($request, $user);

        $this->assertStringEndsWith('/profile/two-factor', $response->getTargetUrl());
        $this->assertSame('/reports', $pending->setupDestination($request));
    }

    public function test_password_login_redirects_to_the_intended_destination_when_setup_is_not_forced(): void
    {
        $user = new GenericUser(['id' => 8, 'email' => 'person@example.com']);
        $manager = new LoginFlowManager(
            $this->pendingTwoFactorLogin(),
            $this->routeResolver(),
            $this->twoFactorStateResolver(false, false),
        );
        $request = $this->makeRequestWithSession();
        $guard = Mockery::mock();
        $guard->shouldReceive('login')->once()->with($user, false);
        Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

        $response = $manager->completePasswordLogin($request, $user);

        $this->assertStringEndsWith('/dashboard', $response->getTargetUrl());
    }

    public function test_trusted_provider_login_sets_and_clears_the_trust_marker(): void
    {
        $user = new GenericUser(['id' => 9, 'email' => 'trusted@example.com']);
        $manager = new LoginFlowManager(
            $this->pendingTwoFactorLogin(),
            $this->routeResolver(),
            $this->twoFactorStateResolver(false, false),
        );
        $request = $this->makeRequestWithSession();
        $guard = Mockery::mock();
        $guard->shouldReceive('login')->once()->with($user);
        Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

        $response = $manager->completeWithTrustedProvider($request, $user);

        $this->assertStringEndsWith('/dashboard', $response->getTargetUrl());
        $this->assertTrue($manager->hasTrustedProvider($request));

        $manager->clearTrustedProvider($request);
        $this->assertFalse($manager->hasTrustedProvider($request));
    }

    private function makeRequestWithSession(string $uri = '/'): Request
    {
        $request = Request::create($uri, 'GET');
        $session = new Store('test', new ArraySessionHandler(120));
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }

    /**
     * @param  array<int|string, Authenticatable>  $users
     */
    private function pendingTwoFactorLogin(array $users = []): PendingTwoFactorLogin
    {
        return new PendingTwoFactorLogin($this->routeResolver(), $this->pendingUserResolver($users));
    }

    /**
     * @param  array<int|string, Authenticatable>  $users
     */
    private function pendingUserResolver(array $users = []): PendingTwoFactorUserResolver
    {
        return new class($users) implements PendingTwoFactorUserResolver
        {
            /**
             * @param  array<int|string, Authenticatable>  $users
             */
            public function __construct(
                private readonly array $users,
            ) {
            }

            public function resolve(int|string $identifier): ?Authenticatable
            {
                return $this->users[$identifier] ?? null;
            }
        };
    }

    private function twoFactorStateResolver(bool $verified, bool $forced): TwoFactorStateResolver
    {
        return new class($verified, $forced) implements TwoFactorStateResolver
        {
            public function __construct(
                private readonly bool $verified,
                private readonly bool $forced,
            ) {
            }

            public function hasVerifiedTwoFactor(Authenticatable $user): bool
            {
                return $this->verified;
            }

            public function isTwoFactorSetupForced(): bool
            {
                return $this->forced;
            }
        };
    }

    private function routeResolver(): AuthRouteResolver
    {
        return new class implements AuthRouteResolver
        {
            public function login(): string
            {
                return '/login';
            }

            public function dashboard(): string
            {
                return '/dashboard';
            }

            public function profile(): string
            {
                return '/profile';
            }

            public function twoFactorChallenge(): string
            {
                return '/login/two-factor';
            }

            public function twoFactorCancel(): string
            {
                return '/login/two-factor/cancel';
            }

            public function twoFactorSetup(): string
            {
                return '/profile/two-factor';
            }
        };
    }
}
