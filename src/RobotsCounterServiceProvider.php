<?php

namespace LinhHa\RobotsCounter;

use Illuminate\Support\ServiceProvider;
use LinhHa\RobotsCounter\app\Console\Commands\RobotsCounterReportCommand;
use LinhHa\RobotsCounter\app\Middleware\RobotsCounterMiddleware;

class RobotsCounterServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {

        $this->loadRoutesFrom(__DIR__ . '/routes/robots_counter_api.php');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->publishes([
            __DIR__ . '/config/robots_counter.php' => config_path('robots_counter.php'),
        ]);
        $router->aliasMiddleware('robots.counter', RobotsCounterMiddleware::class);

        if (! \Config::get('logging.channels.robot_counter_log')) {
            \Config::set('logging.channels.robot_counter_log', \Config::get('robots_counter.log_channel_config'));
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // register the artisan commands
        $this->commands([RobotsCounterReportCommand::class]);
    }

}
