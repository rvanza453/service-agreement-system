<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        $selectedSystems = collect($request->input('systems', []))
            ->filter(fn ($system) => filled($system))
            ->values()
            ->all();

        if (empty($selectedSystems) && $request->filled('system')) {
            $selectedSystems = [(string) $request->input('system')];
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('route_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($selectedSystems)) {
            $query->whereIn('system', $selectedSystems);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(20)->withQueryString();
        
        $users = \Modules\PrSystem\Models\User::orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $systems = ActivityLog::whereNotNull('system')
            ->select('system')
            ->distinct()
            ->orderBy('system')
            ->pluck('system');

        return view('prsystem::admin.activity_logs.index', compact('logs', 'users', 'actions', 'systems', 'selectedSystems'));
    }
}
