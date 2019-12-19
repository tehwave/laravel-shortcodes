<?php

namespace tehwave\Shortcodes\Providers;

use Illuminate\Support\ServiceProvider;
use tehwave\Achievements\Console\Commands\MakeShortcode;

class ShortcodesServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/shortcodes.php' => config_path('shortcodes.php'),
        ], 'shortcodes-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeShortcode::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/shortcodes.php', 'shortcodes');
    }
}
