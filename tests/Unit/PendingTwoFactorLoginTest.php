<?php

namespace Laradeauth\Tests\Unit;

use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Contracts\PendingTwoFactorUserResolver;
use Laradeauth\Support\PendingTwoFactorLogin;
use Laradeauth\Tests\TestCase;

class PendingTwoFactorLoginTest extends TestCase
{
    public function test_it_restores_the_pending_user_and_remember_flag(): void
    {
        $user = new GenericUser(['id' => 42, 'email' => 'tester@example.com']);
        $service = new PendingTwoFactorLogin($this->routeResolver(), $this->pendingUserResolver([
            42 => $user,
        ]));
        $request = $this->makeRequestWithSession();

        $request->session()->put('url.intended', '/dashboard');
        $service->startChallenge($request, $user, true);

        $this->assertTrue($service->hasPending($request));
        $this->assertTrue($service->remember($request));
        $this->assertSame($user->getAuthIdentifier(), $service->pendingUser($request)?->getAuthIdentifier());
        $this->assertSame('/dashboard', $service->destination($request));
    }

    public function test_it_rejects_external_and_auth_destinations(): void
    {
        $service = new PendingTwoFactorLogin($this->routeResolver(), $this->pendingUserResolver());
        $request = $this->makeRequestWithSession();

        $this->assertSame('/dashboard', $service->sanitizeDestination('https://evil.example'));
        $this->assertSame('/dashboard', $service->sanitizeDestination('/login'));
        $this->assertSame('/dashboard', $service->sanitizeDestination('/login/two-factor'));
    }

    public function test_it_remembers_and_clears_setup_destination(): void
    {
        $service = new PendingTwoFactorLogin($this->routeResolver(), $this->pendingUserResolver());
        $request = $this->makeRequestWithSession('/reports');

        $service->rememberSetupDestination($request);
        $this->assertSame('/reports', $service->setupDestination($request));

        $service->clearSetupDestination($request);
        $this->assertSame('/dashboard', $service->setupDestination($request));
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
     * @param  array<int|string, GenericUser>  $users
     */
    private function pendingUserResolver(array $users = []): PendingTwoFactorUserResolver
    {
        return new class($users) implements PendingTwoFactorUserResolver
        {
            /**
             * @param  array<int|string, GenericUser>  $users
             */
            public function __construct(
                private readonly array $users,
            ) {
            }

            public function resolve(int|string $identifier): ?GenericUser
            {
                return $this->users[$identifier] ?? null;
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
