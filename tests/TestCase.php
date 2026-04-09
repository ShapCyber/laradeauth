<?php

namespace Laradeauth\Tests;

use Laradeauth\LaradeauthServiceProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaradeauthServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('session.driver', 'array');
        $app['config']->set('laradeauth.routes', [
            'login' => 'login',
            'dashboard' => 'dashboard',
            'profile' => 'profile.edit',
            'two_factor_challenge' => 'two-factor.challenge',
            'two_factor_cancel' => 'two-factor.challenge.cancel',
            'two_factor_setup' => 'two-factor.setup',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['session']->start();
    }
}
