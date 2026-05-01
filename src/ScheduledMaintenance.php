<?php

namespace Emmanpbarrameda\ScheduledMaintenance;

use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceCancelled;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceCompleted;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceStarted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ScheduledMaintenance
{
    protected Model $model;

    public function __construct()
    {
        $this->model = new (config('scheduled-maintenance.model'));
    }

    public function isDown(): bool
    {
        return $this->current() !== null;
    }

    public function up(): void
    {
        $current = $this->current();

        if ($current) {
            event(new MaintenanceCompleted($current));

            $current->update([
                'is_active' => false,
            ]);

            $current->delete();
        }
    }

    public function down($params = [])
    {
        if ($this->isDown() || app()->isDownForMaintenance()) {
            return false;
        }

        $model = $this->next();

        if (! $model) {
            $model = $this->model->create(
                array_merge([
                    'starts_at' => now(),
                ], Arr::except($params, $this->model->getGuarded()))
            );
        }

        $model->update([
            'is_active' => true,
        ]);

        event(new MaintenanceStarted($model, ! $model->wasRecentlyCreated));

        return $model;
    }

    public function find($id)
    {
        return $this->model->where('id', $id)->orWhere('uuid', $id)->withTrashed()->first();
    }

    public function delete($id)
    {
        $model = $this->model->where('id', $id)->orWhere('uuid', $id)->first();

        if ($model) {
            event(new MaintenanceCancelled($model));

            $model->delete();

            return true;
        }

        return false;
    }

    public function current()
    {
        return $this->model->where('is_active', true)->first();
    }

    public function scheduled()
    {
        return $this->model->where('starts_at', '>=', now())->orderBy('id')->get();
    }

    public function next()
    {
        return $this->model->where('is_active', false)->where('starts_at', '>=', now())->orderBy('id')->first();
    }

    public function notice()
    {
        return $this->model->where('starts_at', '>=', now())->where('display_notice_at', '<=', now())->orderBy('id')->first();
    }

    public function inBypassMode(): bool
    {
        return $this->isDown() && request()->cookies->has(config('scheduled-maintenance.bypass_cookie_name'));
    }
}
