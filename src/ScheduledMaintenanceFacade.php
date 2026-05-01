<?php

namespace Emmanpbarrameda\ScheduledMaintenance;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Emmanpbarrameda\ScheduledMaintenance\ScheduledMaintenance
 */
class ScheduledMaintenanceFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'maintenance';
    }
}