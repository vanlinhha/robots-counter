<?php

namespace LinhHa\RobotsCounter;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use LinhHa\RobotsCounter\Console\Commands\RobotsCounterReportCommand;
use LinhHa\RobotsCounter\Middleware\RobotsCounterMiddleware;

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

        $log_channel_config = [
            'driver' => 'daily',
            'level' => 'emergency',
            'path' => storage_path('logs/robots.log'),
            'days' => 30,
        ];

        if (!Config::get('logging.channels.robot_counter_log')) {
            Config::set('logging.channels.robot_counter_log', $log_channel_config);
        }
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('robot:report --date=today')->daily();
            });
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
