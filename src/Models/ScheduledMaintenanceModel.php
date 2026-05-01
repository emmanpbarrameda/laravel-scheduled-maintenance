<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ScheduledMaintenanceModel extends Model implements ScheduledMaintenanceInterface
{
    use SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'display_notice_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('scheduled-maintenance.table_name', 'scheduled_maintenance');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function redirectTo(): ?string
    {
        return Arr::get($this->settings, 'redirect_to', config('scheduled-maintenance.redirect_to'));
    }

    public function statusCode(): int
    {
        return (int) Arr::get($this->settings, 'status_code', config('scheduled-maintenance.status_code', 503));
    }

    public function bypassSecret(): ?string
    {
        return Arr::get($this->settings, 'bypass_secret', config('scheduled-maintenance.bypass_secret'));
    }
}