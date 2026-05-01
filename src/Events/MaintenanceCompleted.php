<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Events;

use Emmanpbarrameda\ScheduledMaintenance\Models\ScheduledMaintenanceModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceCompleted
{
    use Dispatchable;
    use SerializesModels;

    public ScheduledMaintenanceModel $scheduledMaintenance;

    public function __construct(ScheduledMaintenanceModel $scheduledMaintenance)
    {
        $this->scheduledMaintenance = $scheduledMaintenance;
    }
}
