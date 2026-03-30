<?php

namespace Modules\PrSystem\Helpers;

use Modules\PrSystem\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log($action, $description, $subject = null, array $context = [])
    {
        $routeName = $context['route_name'] ?? Request::route()?->getName();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'system' => $context['system'] ?? self::detectSystem($routeName),
            'route_name' => $routeName,
            'http_method' => $context['http_method'] ?? Request::method(),
            'url' => $context['url'] ?? Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function detectSystem(?string $routeName = null): string
    {
        $name = strtolower((string) $routeName);

        if (str_starts_with($name, 'pr.')) {
            return 'PR';
        }

        if (str_starts_with($name, 'qc.')) {
            return 'QC';
        }

        if (str_starts_with($name, 'sas.')) {
            return 'SAS';
        }

        if (str_starts_with($name, 'ispo.') || str_starts_with($name, 'systemispo.')) {
            return 'ISPO';
        }

        if (
            str_starts_with($name, 'management.')
            || str_starts_with($name, 'modules.')
            || str_starts_with($name, 'users.')
            || str_starts_with($name, 'sites.')
            || str_starts_with($name, 'departments.')
            || str_starts_with($name, 'sub-departments.')
            || str_starts_with($name, 'master-departments.')
            || str_starts_with($name, 'activity-logs.')
        ) {
            return 'GLOBAL';
        }

        return 'OTHER';
    }
}
