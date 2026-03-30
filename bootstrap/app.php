<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\LogModuleActivity::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'assigned.role' => \App\Http\Middleware\EnsureUserHasAssignedRole::class,
            'pr.role'   => \Modules\PrSystem\Http\Middleware\PrRoleMiddleware::class,
            'qc.role'   => \Modules\QcComplaintSystem\Http\Middleware\QcRoleMiddleware::class,
            'ispo.role' => \Modules\SystemISPO\Http\Middleware\IspoRoleMiddleware::class,
            'sas.role'  => \Modules\ServiceAgreementSystem\Http\Middleware\SasRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
