<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Commands;

use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceStarted;
use Illuminate\Console\Command;

class MaintenanceActivateCommand extends Command
{
    protected $signature = 'maintenance:activate';

    protected $description = 'Activate any scheduled maintenance windows that are due';

    public function handle(): int
    {
        if (app('maintenance')->isDown() || app()->isDownForMaintenance()) {
            $this->warn('Maintenance mode is already active.');

            return self::SUCCESS;
        }

        $model = new (config('scheduled-maintenance.model'));

        $due = $model
            ->where('is_active', false)
            ->where('starts_at', '<=', now())
            ->whereNull('deleted_at')
            ->first();

        if (! $due) {
            $this->warn('No maintenance windows are ready to activate.');

            return self::SUCCESS;
        }

        $due->update([
            'is_active' => true,
        ]);

        event(new MaintenanceStarted($due, true));

        $this->info("Maintenance window activated: {$due->title}");

        return self::SUCCESS;
    }
}