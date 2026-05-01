<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Jobs;

use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeactivateMaintenanceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int|string $maintenanceId
    ) {}

    public function handle(): void
    {
        $model = new (config('scheduled-maintenance.model'));

        $maintenance = $model
            ->where('id', $this->maintenanceId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $maintenance) {
            return;
        }

        $maintenance->update([
            'is_active' => false,
        ]);

        event(new MaintenanceCompleted($maintenance));

        $maintenance->delete();
    }
}