<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Commands;

use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceScheduled;
use Emmanpbarrameda\ScheduledMaintenance\Jobs\ActivateMaintenanceJob;
use Emmanpbarrameda\ScheduledMaintenance\Jobs\DeactivateMaintenanceJob;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ScheduleMaintenanceCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'maintenance:schedule
        {--title= : Title of the maintenance window}
        {--description= : Description of the maintenance window}
        {--starts-at= : When maintenance starts (Y-m-d H:i:s)}
        {--ends-at= : When maintenance ends (Y-m-d H:i:s)}
        {--notify-at= : When to display notice to users (Y-m-d H:i:s)}
        {--bypass-secret= : Secret for bypassing maintenance mode}
        {--redirect-to= : Redirect users to this URL during maintenance}
        {--status-code= : HTTP status code (default: 503)}';

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
            'title' => $this->option('title')
                ?? $this->ask('Title'),

            'description' => $this->option('description')
                ?? $this->ask('Description'),

            'settings' => [
                'redirect_to' => $this->option('redirect-to')
                    ?? $this->ask('Redirect to', config('scheduled-maintenance.redirect_to')),

                'status_code' => (int) ($this->option('status-code')
                    ?? $this->ask('Status code', config('scheduled-maintenance.status_code', 503))),

                'bypass_secret' => $this->option('bypass-secret')
                    ?? $this->ask(
                        'Bypass secret',
                        config('scheduled-maintenance.bypass_secret') ?? (string) Str::uuid()
                    ),
            ],

            'starts_at' => $this->option('starts-at')
                ?? $this->ask(
                    'Maintenance starts (e.g. "2026-05-08 05:00:00") — default is ' . $startsAt->diffForHumans(),
                    $startsAt->toDateTimeString()
                ),

            'ends_at' => $this->option('ends-at')
                ?? $this->ask(
                    'Maintenance ends (e.g. "2026-05-09 07:00:00") — default is ' . $endsAt->diffForHumans(),
                    $endsAt->toDateTimeString()
                ),

            'display_notice_at' => $this->option('notify-at')
                ?? $this->ask(
                    'When should users see a notice? — default is ' . $displayNoticeAt->diffForHumans(),
                    $displayNoticeAt->toDateTimeString()
                ),
        ]);

        event(new MaintenanceScheduled($scheduled));

        ActivateMaintenanceJob::dispatch()->delay(Carbon::parse($scheduled->starts_at));
        DeactivateMaintenanceJob::dispatch($scheduled->id)->delay(Carbon::parse($scheduled->ends_at));

        $this->info('Maintenance scheduled!');
        $this->info('Bypass URL: ' . url($scheduled->bypassSecret()));

        return self::SUCCESS;
    }
}