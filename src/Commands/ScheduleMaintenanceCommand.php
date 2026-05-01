<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Commands;

use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceScheduled;
use Emmanpbarrameda\ScheduledMaintenance\Jobs\ActivateMaintenanceJob;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ScheduleMaintenanceCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'maintenance:schedule';

    protected $description = 'Schedule a new maintenance window';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $model = new (config('scheduled-maintenance.model'));

        $startsAt = now()->addWeek()->setTime(5, 0, 0);
        $endsAt = now()->addWeek()->addDay()->setTime(7, 0, 0);
        $displayNoticeAt = now()->addDays(5)->setTime(8, 0, 0);

        $scheduled = $model->create([
            'title' => $this->ask('Title'),
            'description' => $this->ask('Description'),
            'settings' => [
                'redirect_to' => $this->ask('Redirect to', config('scheduled-maintenance.redirect_to')),
                'status_code' => (int) $this->ask('Status', config('scheduled-maintenance.status_code', 503)),
                'bypass_secret' => $this->ask(
                    'Secret for bypassing maintenance mode',
                    config('scheduled-maintenance.bypass_secret') ?? (string) Str::uuid()
                ),
            ],
            'starts_at' => $this->ask('Maintenance Starts', $startsAt->toDateTimeString()),
            'ends_at' => $this->ask('Maintenance Ends', $endsAt->toDateTimeString()),
            'display_notice_at' => $this->ask('When should users see a notice about this maintenance window?', $displayNoticeAt->toDateTimeString()),
        ]);

        event(new MaintenanceScheduled($scheduled));

        ActivateMaintenanceJob::dispatch()->delay(Carbon::parse($scheduled->starts_at));

        $this->info('Maintenance scheduled!');

        return self::SUCCESS;
    }
}
