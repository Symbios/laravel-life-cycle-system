<?php

namespace Abix\SystemLifeCycle;

use Abix\SystemLifeCycle\Commands\CreateSystemLifeCycleCommand;
use Abix\SystemLifeCycle\Commands\SystemLifeCycleLogsCleanUpCommand;
use Abix\SystemLifeCycle\Commands\SystemLifeCycleModelCleanUpCommand;
use Abix\SystemLifeCycle\Commands\SystemLifeCycleRunCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class SystemLifeCycleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
        $this->registerMigrations();

        $this->publishes([
            __DIR__ . '/config/systemLifeCycle.php' => config_path('systemLifeCycle.php'),
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('system-life-cycle:run')->hourly();
            $schedule->command('system-life-cycle:logs-clean-up')->daily();
            $schedule->command('system-life-cycle:completed-models-clean-up')->daily();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/systemLifeCycle.php',
            'systemLifeCycle'
        );
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/migrations');
        }
    }


    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SystemLifeCycleRunCommand::class,
                SystemLifeCycleLogsCleanUpCommand::class,
                SystemLifeCycleModelCleanUpCommand::class,
                CreateSystemLifeCycleCommand::class
            ]);
        }
    }
}
