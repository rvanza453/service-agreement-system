<?php

namespace Modules\PrSystem\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/pr-dashboard';

    protected string $name = 'PrSystem';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        // Note: Auth routes (login/register/logout) are intentionally NOT loaded here
        // because the main application already handles authentication.
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }
}
