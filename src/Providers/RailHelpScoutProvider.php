<?php

namespace Railroad\RailHelpScout\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class RailHelpScoutProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->publishes(
            [
                __DIR__ . '/../../config/railhelpscout.php' => config_path('railhelpscout.php'),
            ]
        );

        if (config('railhelpscout.data_mode') == 'host') {
            $this->loadMigrationsFrom(__DIR__.'/../../migrations');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
