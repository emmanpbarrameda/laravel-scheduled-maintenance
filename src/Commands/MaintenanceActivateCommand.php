<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Commands;

use Illuminate\Console\Command;

class MaintenanceActivateCommand extends Command
{
    protected $signature = 'maintenance:activate';
    protected $description = 'Activate any scheduled maintenance windows that are due';

    public function handle(): int
    {
        if (app('maintenance')->isDown() || app()->isDownForMaintenance()) {
            return self::SUCCESS;
        }

        $model = new (config('scheduled-maintenance.model'));

        $due = $model
            ->where('is_active', false)
            ->where('starts_at', '<=', now())
            ->whereNull('deleted_at')
            ->first();

        if ($due) {
            app('maintenance')->down();
            $this->info("Maintenance window activated: {$due->title}");
        }

        return self::SUCCESS;
    }
}