<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ModuleHubController extends Controller
{
    public function index(): View
    {
        $modules = [
            [
                'name' => 'Service Agreement System',
                'description' => 'Kelola kontraktor, USPK submission, dan workflow approval.',
                'route' => route('sas.dashboard'),
                'icon' => 'fa-screwdriver-wrench',
                'accent' => 'steel',
                'disabled' => true,
            ],
            [
                'name' => 'QC Complaint System',
                'description' => 'Pelaporan temuan QC, tracking penyelesaian, dan approval close.',
                'route' => route('qc.dashboard'),
                'icon' => 'fa-clipboard-check',
                'accent' => 'amber',
            ],
            [
                'name' => 'System ISPO',
                'description' => 'Dokumentasi dan audit kepatuhan Indonesian Sustainable Palm Oil (ISPO).',
                'route' => route('ispo.index'),
                'icon' => 'fa-leaf',
                'accent' => 'green',
            ],
            [
                'name' => 'User & Role Management',
                'description' => 'Kelola user, role, master organisasi, dan log aktivitas lintas modul.',
                'route' => route('management.dashboard'),
                'icon' => 'fa-users-gear',
                'accent' => 'steel',
            ],
            [
                'name' => 'Purchase Request System',
                'description' => 'Sistem Purchase Request, PO, dan Request Capex.',
                'route' => route('pr.dashboard'),
                'icon' => 'fa-shopping-cart',
                'accent' => 'amber',
                'disabled' => true,
            ],
        ];

        return view('modules.index', [
            'modules' => $modules,
        ]);
    }
}
