<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\PrSystem\Helpers\ActivityLogger;
use Throwable;

class LogModuleActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!Auth::check()) {
            return $response;
        }

        if ($request->routeIs('activity-logs.*')) {
            return $response;
        }

        $route = $request->route();

        if (!$route) {
            return $response;
        }

        $routeName = $route->getName();
        $method = strtoupper($request->method());

        $action = match ($method) {
            'GET' => 'accessed',
            'POST' => 'submitted',
            'PUT', 'PATCH' => 'updated-request',
            'DELETE' => 'deleted-request',
            default => 'request',
        };

        $description = sprintf(
            '[%s] %s (%d)',
            $method,
            $routeName ?: $request->path(),
            $response->getStatusCode()
        );

        try {
            ActivityLogger::log($action, $description, null, [
                'system' => ActivityLogger::detectSystem($routeName),
                'route_name' => $routeName,
                'http_method' => $method,
                'url' => $request->fullUrl(),
            ]);
        } catch (Throwable) {
            // Never block user flow when activity logging fails.
        }

        return $response;
    }
}
