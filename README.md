# Laravel Scheduled Maintenance

A modern Laravel package to schedule maintenance windows, notify users about upcoming maintenance, and customize the experience while your app is down.

> Fork of [churchportal/laravel-scheduled-maintenance](https://github.com/churchportal/laravel-scheduled-maintenance) — modernized for Laravel 10/11/12/13 and PHP 8.1+.

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | ^8.1 (up to latest version) |
| Laravel | ^10.0 \| ^11.0 \| ^12.0 |

---

## Installation

```bash
composer require emmanpbarrameda/laravel-scheduled-maintenance
```

Publish the config, migration, and view:

```bash
php artisan vendor:publish --provider="Emmanpbarrameda\\ScheduledMaintenance\\ScheduledMaintenanceServiceProvider"
```

Run the migration:

```bash
php artisan migrate
```

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

## Configuration

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

## Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan maintenance:schedule` | Interactively schedule a new maintenance window |
| `php artisan maintenance:down` | Immediately put the app into maintenance mode |
| `php artisan maintenance:up` | Bring the app out of maintenance mode |
| `php artisan maintenance:upcoming` | List all upcoming maintenance windows |
| `php artisan maintenance:cancel {id}` | Cancel a scheduled maintenance window |

### `maintenance:down` Options

```bash
php artisan maintenance:down --bypass-secret=mysecret --redirect-to=/maintenance
```

---

## The `app('maintenance')` Singleton

```php
app('maintenance')->isDown();        // bool — is app currently in maintenance?
app('maintenance')->down();          // put app into maintenance mode
app('maintenance')->up();            // bring app out of maintenance mode
app('maintenance')->current();       // get the active maintenance window model
app('maintenance')->next();          // get the next scheduled maintenance window
app('maintenance')->scheduled();     // get all future maintenance windows
app('maintenance')->find($id);       // find a window by id or uuid
app('maintenance')->delete($id);     // delete a window by id or uuid
app('maintenance')->notice();        // get upcoming notice (if display_notice_at has passed)
app('maintenance')->inBypassMode();  // bool — has the user bypassed maintenance?
```

---

## Usage

### Notify Users About Upcoming Maintenance

Add this to your layout blade file:

```blade
@if(app('maintenance')->notice())
    <div class="alert alert-warning">
        We'll be performing maintenance on
        {{ app('maintenance')->notice()->starts_at->format('F jS, \\a\\t g:ia') }}.
        Please save your work before then.
    </div>
@endif
```

---

### Show Bypass Banner to Developers

When you visit the `bypass_secret` URL, a cookie is set so you can access the app normally during maintenance. You can show a reminder banner:

```blade
@if(app('maintenance')->inBypassMode())
    <div class="alert alert-danger">
        This app is currently in maintenance mode.
        It should be back up by {{ app('maintenance')->current()->ends_at?->format('F jS, \\a\\t g:ia') ?? 'soon' }}.
    </div>
@endif
```

---

### Check Maintenance Status in a Controller

```php
if (app('maintenance')->isDown()) {
    return response()->view('errors.maintenance', [], 503);
}
```

---

### Listen to Maintenance Events

In your `AppServiceProvider`:

```php
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceStarted;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceCompleted;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceCancelled;
use Emmanpbarrameda\ScheduledMaintenance\Events\MaintenanceScheduled;

Event::listen(MaintenanceStarted::class, function ($event) {
    // $event->scheduledMaintenance — the model
    // $event->wasPreviouslyScheduled — bool
});

Event::listen(MaintenanceCompleted::class, function ($event) {
    // app is back up
});
```

---

## Custom Maintenance View

After publishing, edit the view to match your brand:

```
resources/views/vendor/scheduled-maintenance/down.blade.php
```

---

## Events Reference

| Event | Trigger | Extra Property |
|-------|---------|----------------|
| `MaintenanceScheduled` | After `maintenance:schedule` | — |
| `MaintenanceStarted` | After `app('maintenance')->down()` | `$wasPreviouslyScheduled` |
| `MaintenanceCompleted` | After `app('maintenance')->up()` | — |
| `MaintenanceCancelled` | After `app('maintenance')->delete($id)` | — |

All events expose a public `$scheduledMaintenance` model property.

---

## License

MIT — see [LICENSE](LICENSE) for details.

---

## Credits

- Original package by [James Burrow / churchportal](https://github.com/churchportal/laravel-scheduled-maintenance)
- Modernized and maintained by [Emman P. Barrameda](https://github.com/emmanpbarrameda)
