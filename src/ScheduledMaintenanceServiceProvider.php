<?php

namespace Emmanpbarrameda\ScheduledMaintenance;

use Emmanpbarrameda\ScheduledMaintenance\Commands\MaintenanceActivateCommand;
use Emmanpbarrameda\ScheduledMaintenance\Commands\MaintenanceCancelCommand;
use Emmanpbarrameda\ScheduledMaintenance\Commands\MaintenanceDownCommand;
use Emmanpbarrameda\ScheduledMaintenance\Commands\MaintenanceUpcomingCommand;
use Emmanpbarrameda\ScheduledMaintenance\Commands\MaintenanceUpCommand;
use Emmanpbarrameda\ScheduledMaintenance\Commands\ScheduleMaintenanceCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScheduledMaintenanceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-scheduled-maintenance')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_scheduled_maintenance_table')
            ->hasCommands([
                ScheduleMaintenanceCommand::class,
                MaintenanceDownCommand::class,
                MaintenanceUpCommand::class,
                MaintenanceUpcomingCommand::class,
                MaintenanceCancelCommand::class,
                MaintenanceActivateCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('maintenance', ScheduledMaintenance::class);
    }


    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/scheduled-maintenance'),
        ], 'laravel-scheduled-maintenance-assets');
    }
}