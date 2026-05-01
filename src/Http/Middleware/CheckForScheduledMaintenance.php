<?php

namespace Emmanpbarrameda\ScheduledMaintenance\Http\Middleware;

use Closure;
use Emmanpbarrameda\ScheduledMaintenance\ScheduledMaintenanceModeBypassCookie;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForScheduledMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        $maintenance = app('maintenance');

        if (! $maintenance->isDown()) {
            $response = $next($request);

            if ($request->hasCookie(config('scheduled-maintenance.bypass_cookie_name'))) {
                return $response->withCookie(ScheduledMaintenanceModeBypassCookie::remove());
            }

            return $response;
        }

        $current = $maintenance->current();

        if (! $current) {
            return $next($request);
        }

        $bypassSecret = $current->bypassSecret();
        $statusCode = $current->statusCode();

        if ($bypassSecret && $request->path() === $bypassSecret) {
            return $this->bypassResponse($bypassSecret);
        }

        if ($this->hasValidBypassCookie($request, $bypassSecret) || $this->isExcepted($request)) {
            return $next($request);
        }

        $redirectTo = $current->redirectTo();

        if ($redirectTo) {
            $path = $redirectTo === '/' ? '/' : trim($redirectTo, '/');

            if ($request->path() !== $path) {
                return redirect($path)->setStatusCode($statusCode);
            }
        }

        return response()
            ->view(config('scheduled-maintenance.view', 'scheduled-maintenance::down'), [], $statusCode);
    }

    protected function bypassResponse(string $secret): Response
    {
        return redirect('/')
            ->withCookie(ScheduledMaintenanceModeBypassCookie::create($secret));
    }

    protected function hasValidBypassCookie(Request $request, ?string $bypassSecret): bool
    {
        if (! $bypassSecret) {
            return false;
        }

        $cookie = $request->cookie(config('scheduled-maintenance.bypass_cookie_name'));

        if (! $cookie) {
            return false;
        }

        return ScheduledMaintenanceModeBypassCookie::isValid($cookie, $bypassSecret);
    }

    protected function isExcepted(Request $request): bool
    {
        foreach (config('scheduled-maintenance.except', []) as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
