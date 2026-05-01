# Laravel Scheduled Maintenance

A modern Laravel package for scheduling app maintenance, showing upcoming downtime notices, and customizing the maintenance page. Supports Laravel 10, 11, and 12 with PHP 8.1 and newer.

> A Fork of [laravel-scheduled-maintenance by James Burrow](https://github.com/churchportal/laravel-scheduled-maintenance)

---

# Requirements

| Requirement | Version |
|-------------|---------|
| PHP | ^8.1 (up to latest) |
| Laravel | ^10.0 \| ^11.0 \| ^12.0 |

---

# Installation

## Step 1
```bash
composer require emmanpbarrameda/laravel-scheduled-maintenance:^1.0.2
```

## Step 2
Publish the config, migration, views, and assets:

```bash
php artisan vendor:publish --provider="Emmanpbarrameda\ScheduledMaintenance\ScheduledMaintenanceServiceProvider"
```

## Step 3
Run the migration:

```bash
php artisan migrate
```

## Step 4
Register the middleware in `bootstrap/app.php` (Laravel 11+):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->prepend(\Emmanpbarrameda\ScheduledMaintenance\Http\Middleware\CheckForScheduledMaintenance::class);
})
```

Or in `app/Http/Kernel.php` (Laravel 10):

```php
protected $middleware = [
    \Emmanpbarrameda\ScheduledMaintenance\Http\Middleware\CheckForScheduledMaintenance::class,
    // ...
];
```

---

# Configuration

After publishing, edit `config/scheduled-maintenance.php`:

```php
return [
    'table_name'         => 'scheduled_maintenance',
    'model'              => \Emmanpbarrameda\ScheduledMaintenance\Models\ScheduledMaintenanceModel::class,
    'redirect_to'        => null,
    'status_code'        => 503,
    'bypass_secret'      => null,
    'bypass_cookie_name' => env('SCHEDULED_MAINTENANCE_BYPASS_COOKIE', 'scheduled_maintenance_bypass'),
    'except'             => ['status'],
    'view'               => 'scheduled-maintenance::down',
];
```

> **Note:** This package relies on your database. If you are performing significant DB work during a maintenance window, consider using Laravel's native `php artisan down` command instead.

---

# Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan maintenance:schedule` | Interactively schedule a new maintenance window |
| `php artisan maintenance:down` | Immediately put the app into maintenance mode |
| `php artisan maintenance:up` | Bring the app out of maintenance mode |
| `php artisan maintenance:upcoming` | List all upcoming maintenance windows |
| `php artisan maintenance:cancel {id}` | Cancel a scheduled maintenance window |
| `php artisan maintenance:activate` | Manually activate a maintenance window |

### `maintenance:down` Options

```bash
php artisan maintenance:down --bypass-secret=mysecret --redirect-to=/maintenance
```

---

# Auto-Maintenance Activation via Queue

Scheduled maintenance windows dispatch a delayed job that activates maintenance at `starts_at` column.

<b>Make sure a queue worker is running via:</b>

```bash
php artisan queue:work
```

---

# The `app('maintenance')` Singleton

```php
app('maintenance')->isDown();        // bool - is app currently in maintenance?
app('maintenance')->down();          // put app into maintenance mode
app('maintenance')->up();            // bring app out of maintenance mode
app('maintenance')->current();       // get the active maintenance window model
app('maintenance')->next();          // get the next scheduled maintenance window
app('maintenance')->scheduled();     // get all future maintenance windows
app('maintenance')->find($id);       // find a window by id or uuid
app('maintenance')->delete($id);     // delete a window by id or uuid
app('maintenance')->notice();        // get upcoming notice (if display_notice_at has passed)
app('maintenance')->inBypassMode();  // bool - has the user bypassed maintenance?
```
> The package registers a Laravel singleton service using the `maintenance` key. You can access it anywhere in your Laravel app using `app('maintenance')`.

---

# Blade Components Banners

The package ships with two ready-to-use Blade components. Add them to your <b>main layout</b> (e.g: `welcome.blade.php`):

```blade
<x-scheduled-maintenance::bypass-banner />
<x-scheduled-maintenance::notice-banner />
```

### Bypass Banner
Shows a fixed top banner when a developer is bypassing maintenance mode. Styled with inline CSS - no Tailwind required.

### Notice Banner
Shows a warning banner when an upcoming maintenance window's `display_notice_at` time has passed, notifying users before the maintenance begins.

> After running `vendor:publish`, you can customize both banners at:
> `resources/views/vendor/scheduled-maintenance/components/`

---

## Banner Usage

## 1. Notify Users About Upcoming Maintenance

Using the built-in component (recommended):

```blade
<x-scheduled-maintenance::notice-banner />
```

Or manually in your layout:

```blade
@php($maintenanceNotice = app('maintenance')->notice())
@if($maintenanceNotice)
    <div>
        Scheduled maintenance on
        {{ $maintenanceNotice->starts_at->format('F jS, \\a\\t g:ia') }}.
        Please save your work before then.
    </div>
@endif
```

---

## 2. Show Bypass Banner to Developers

Using the built-in component (recommended):

```blade
<x-scheduled-maintenance::bypass-banner />
```

Or manually:

```blade
@if(app('maintenance')->inBypassMode())
    <div>
        This app is currently in maintenance mode.
        Back up by {{ app('maintenance')->current()->ends_at?->format('F jS, \\a\\t g:ia') ?? 'soon' }}.
    </div>
@endif
```

---

# Bypass Maintenance Mode

Navigate to your `bypass_secret` URL to set a bypass cookie (valid 12 hours). Only users with the cookie can access the app while it is down.

---

# Check Maintenance Status in a Controller

```php
if (app('maintenance')->isDown()) {
    return response()->view('errors.maintenance', [], 503);
}
```

---

# Listen to Maintenance Events

In your `AppServiceProvider`:

```php
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceStarted;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceCompleted;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceCancelled;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceScheduled;

Event::listen(MaintenanceStarted::class, function ($event) {
    // $event->scheduledMaintenance - the model
    // $event->wasPreviouslyScheduled - bool
});

Event::listen(MaintenanceCompleted::class, function ($event) {
    // app is back up
});
```

---

# Custom Maintenance View

After publishing, edit the view to match your brand:

```
resources/views/vendor/scheduled-maintenance/down.blade.php
```

The default view includes a two-column layout with a countdown timer and an illustration. Replace `public/vendor/scheduled-maintenance/maintenance.svg` with your own illustration if you want.

---

# Events Reference

| Event | Trigger | Extra Property |
|-------|---------|----------------|
| `MaintenanceScheduled` | After `maintenance:schedule` | - |
| `MaintenanceStarted` | After `app('maintenance')->down()` | `$wasPreviouslyScheduled` |
| `MaintenanceCompleted` | After `app('maintenance')->up()` | - |
| `MaintenanceCancelled` | After `app('maintenance')->delete($id)` | - |

All events expose a public `$scheduledMaintenance` model property.

---

# License

MIT - see [LICENSE](https://github.com/emmanpbarrameda/laravel-scheduled-maintenance?tab=MIT-1-ov-file) for details.

---

# Credits

- Original package by [James Burrow](https://github.com/churchportal/laravel-scheduled-maintenance)
- Modernized and maintained by [Emman P. Barrameda](https://github.com/emmanpbarrameda)