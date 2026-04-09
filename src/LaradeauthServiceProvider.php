<?php

namespace Laradeauth;

use Illuminate\Support\ServiceProvider;
use Laradeauth\Contracts\AuthRouteResolver;
use Laradeauth\Support\ConfigAuthRouteResolver;

class LaradeauthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laradeauth.php', 'laradeauth');

        $this->app->singletonIf(AuthRouteResolver::class, ConfigAuthRouteResolver::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/laradeauth.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laradeauth');

        $this->publishes([
            __DIR__.'/../config/laradeauth.php' => config_path('laradeauth.php'),
        ], 'laradeauth-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laradeauth'),
        ], 'laradeauth-views');
    }
}
