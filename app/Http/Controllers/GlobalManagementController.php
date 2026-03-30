<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GlobalManagementController extends Controller
{
    public function index(): View
    {
        $items = [
            [
                'title' => 'Manajemen Pengguna',
                'description' => 'Kelola akun aplikasi serta assignment role per modul.',
                'route' => route('users.index'),
                'icon' => 'fa-user-shield',
            ],
            [
                'title' => 'Master Site',
                'description' => 'Kelola daftar site yang dipakai lintas modul.',
                'route' => route('sites.index'),
                'icon' => 'fa-map-location-dot',
            ],
            [
                'title' => 'Master Unit',
                'description' => 'Kelola unit organisasi (master department).',
                'route' => route('master-departments.index'),
                'icon' => 'fa-building',
            ],
            [
                'title' => 'Master Department',
                'description' => 'Kelola department per site untuk operasional modul.',
                'route' => route('departments.index'),
                'icon' => 'fa-diagram-project',
            ],
            [
                'title' => 'Master Sub Department',
                'description' => 'Kelola sub department (afdeling) per department.',
                'route' => route('sub-departments.index'),
                'icon' => 'fa-sitemap',
            ],
            [
                'title' => 'Log Aktivitas',
                'description' => 'Pantau histori aktivitas perubahan data oleh pengguna.',
                'route' => route('activity-logs.index'),
                'icon' => 'fa-clock-rotate-left',
            ],
        ];

        return view('management.index', [
            'items' => $items,
        ]);
    }
}
