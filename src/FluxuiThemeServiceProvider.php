<?php

namespace AgenticMorf\FluxuiTheme;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class FluxuiThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AppearanceService::class, function ($app) {
            $resolver = config('fluxui-theme.appearance_resolver');
            $defaults = $resolver && is_callable($resolver)
                ? $app->call($resolver)
                : config('fluxui-theme.defaults', []);

            return new AppearanceService($defaults);
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'fluxui-theme');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->mergeConfigFrom(__DIR__.'/../config/fluxui-theme.php', 'fluxui-theme');

        Volt::mount([__DIR__.'/../resources/views/livewire']);

        if (! $this->app->routesAreCached()) {
            Route::middleware(['web', 'auth'])->group(function () {
                Volt::route(
                    config('fluxui-theme.route', 'settings/appearance'),
                    'settings.appearance'
                )->name(config('fluxui-theme.route_name', 'appearance.edit'));
            });
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fluxui-theme.php' => config_path('fluxui-theme.php'),
            ], 'fluxui-theme-config');

            $this->publishes([
                __DIR__.'/../resources/views/components' => resource_path('views/components'),
            ], 'fluxui-theme-views');
        }
    }
}
