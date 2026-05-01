<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Events;

use Emmanpbarrameda\ScheduledMaintenance\Models\ScheduledMaintenanceModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceStarted
{
    use Dispatchable;
    use SerializesModels;

    public ScheduledMaintenanceModel $scheduledMaintenance;

    public bool $wasPreviouslyScheduled;

    public function __construct(ScheduledMaintenanceModel $scheduledMaintenance, bool $wasPreviouslyScheduled = false)
    {
        $this->scheduledMaintenance = $scheduledMaintenance;
        $this->wasPreviouslyScheduled = $wasPreviouslyScheduled;
    }
}