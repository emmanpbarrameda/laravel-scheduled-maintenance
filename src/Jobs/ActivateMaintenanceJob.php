<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Jobs;

use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceStarted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActivateMaintenanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (app('maintenance')->isDown() || app()->isDownForMaintenance()) {
            return;
        }

        $model = new (config('scheduled-maintenance.model'));
        $due = $model
            ->where('is_active', false)
            ->where('starts_at', '<=', now())
            ->whereNull('deleted_at')
            ->first();

        if (! $due) {
            return;
        }

        $due->update(['is_active' => true]);

        event(new MaintenanceStarted($due, true));
    }
}