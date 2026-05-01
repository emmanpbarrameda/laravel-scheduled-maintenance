<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Tests;

use Emmanpbarrameda\ScheduledMaintenance\ScheduledMaintenanceServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Emmanpbarrameda\\ScheduledMaintenance\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ScheduledMaintenanceServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        include_once __DIR__.'/../database/migrations/create_laravel-scheduled-maintenance_table.php.stub';
        (new \CreatePackageTable())->up();
        */
    }
}
