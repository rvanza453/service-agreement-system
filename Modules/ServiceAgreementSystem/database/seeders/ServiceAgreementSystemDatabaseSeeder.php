<?php

namespace Modules\ServiceAgreementSystem\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\ServiceAgreementSystem\Models\Block;
use Modules\ServiceAgreementSystem\Models\Contractor;
use Modules\ServiceAgreementSystem\Models\Department;
use Modules\ServiceAgreementSystem\Models\Job;
use Modules\ServiceAgreementSystem\Models\Site;
use Modules\ServiceAgreementSystem\Models\SubDepartment;
use Modules\ServiceAgreementSystem\Models\UspkBudgetActivity;
use Spatie\Permission\Models\Role;

class ServiceAgreementSystemDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = ['Admin', 'Asisten Afdeling', 'Approver', 'QC', 'Manager', 'KTU', 'GM'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Site
        $site = Site::firstOrCreate(
            ['code' => 'SSM'],
            ['name' => 'PT Saraswanti Sawit Makmur', 'location' => 'Kalimantan']
        );

        // Departments
        $deptMJE = Department::firstOrCreate(
            ['site_id' => $site->id, 'coa' => 'MJE'],
            ['name' => 'Kebun MJE', 'budget_type' => 'station', 'budget' => 0]
        );

        $deptKDE = Department::firstOrCreate(
            ['site_id' => $site->id, 'coa' => 'KDE'],
            ['name' => 'Kebun KDE', 'budget_type' => 'station', 'budget' => 0]
        );

        // Sub Departments (Afdeling)
        $afd1 = SubDepartment::firstOrCreate(
            ['department_id' => $deptMJE->id, 'name' => 'Afdeling 1'],
            ['coa' => 'MJE-AFD1']
        );

        $afd2 = SubDepartment::firstOrCreate(
            ['department_id' => $deptMJE->id, 'name' => 'Afdeling 2'],
            ['coa' => 'MJE-AFD2']
        );

        $afd3 = SubDepartment::firstOrCreate(
            ['department_id' => $deptKDE->id, 'name' => 'Afdeling 1'],
            ['coa' => 'KDE-AFD1']
        );

        // Blocks
        $blocks = [
            ['sub_department_id' => $afd1->id, 'name' => 'Blok A1', 'code' => 'A1'],
            ['sub_department_id' => $afd1->id, 'name' => 'Blok A2', 'code' => 'A2'],
            ['sub_department_id' => $afd1->id, 'name' => 'Blok A3', 'code' => 'A3'],
            ['sub_department_id' => $afd2->id, 'name' => 'Blok B1', 'code' => 'B1'],
            ['sub_department_id' => $afd2->id, 'name' => 'Blok B2', 'code' => 'B2'],
            ['sub_department_id' => $afd3->id, 'name' => 'Blok C1', 'code' => 'C1'],
        ];

        foreach ($blocks as $blockData) {
            Block::firstOrCreate(
                ['sub_department_id' => $blockData['sub_department_id'], 'code' => $blockData['code']],
                $blockData
            );
        }

        // Jobs (Aktivitas)
        $jobs = [
            ['site_id' => $site->id, 'code' => 'ACT-001', 'name' => 'Pemeliharaan Jalan'],
            ['site_id' => $site->id, 'code' => 'ACT-002', 'name' => 'Pemeliharaan Jembatan'],
            ['site_id' => $site->id, 'code' => 'ACT-003', 'name' => 'Pembersihan Lahan'],
            ['site_id' => $site->id, 'code' => 'ACT-004', 'name' => 'Penanaman Ulang'],
            ['site_id' => $site->id, 'code' => 'ACT-005', 'name' => 'Pemupukan'],
        ];

        $jobModels = [];
        foreach ($jobs as $jobData) {
            $jobModels[] = Job::firstOrCreate(
                ['code' => $jobData['code']],
                $jobData
            );
        }

        // Budget Activities (per Block per Job)
        $blockA1 = Block::where('code', 'A1')->first();
        $blockA2 = Block::where('code', 'A2')->first();

        if ($blockA1 && isset($jobModels[0])) {
            UspkBudgetActivity::firstOrCreate(
                ['block_id' => $blockA1->id, 'job_id' => $jobModels[0]->id, 'year' => 2026],
                ['budget_amount' => 50000000, 'used_amount' => 0, 'description' => 'Budget pemeliharaan jalan Blok A1 2026']
            );
        }

        if ($blockA1 && isset($jobModels[2])) {
            UspkBudgetActivity::firstOrCreate(
                ['block_id' => $blockA1->id, 'job_id' => $jobModels[2]->id, 'year' => 2026],
                ['budget_amount' => 30000000, 'used_amount' => 0, 'description' => 'Budget pembersihan lahan Blok A1 2026']
            );
        }

        if ($blockA2 && isset($jobModels[0])) {
            UspkBudgetActivity::firstOrCreate(
                ['block_id' => $blockA2->id, 'job_id' => $jobModels[0]->id, 'year' => 2026],
                ['budget_amount' => 45000000, 'used_amount' => 0, 'description' => 'Budget pemeliharaan jalan Blok A2 2026']
            );
        }

        // Contractors
        $contractors = [
            [
                'name' => 'Budi Santoso',
                'company_name' => 'CV Karya Mandiri',
                'npwp' => '12.345.678.9-012.000',
                'address' => 'Jl. Merdeka No. 10, Pontianak',
                'phone' => '081234567890',
                'email' => 'budi@karyamandiri.com',
                'bank_name' => 'BRI',
                'bank_branch' => 'Pontianak',
                'account_number' => '1234567890',
                'account_holder_name' => 'CV Karya Mandiri',
            ],
            [
                'name' => 'Ahmad Yusuf',
                'company_name' => 'PT Maju Bersama',
                'npwp' => '98.765.432.1-098.000',
                'address' => 'Jl. Sudirman No. 25, Ketapang',
                'phone' => '082345678901',
                'email' => 'ahmad@majubersama.co.id',
                'bank_name' => 'BCA',
                'bank_branch' => 'Ketapang',
                'account_number' => '9876543210',
                'account_holder_name' => 'PT Maju Bersama',
            ],
            [
                'name' => 'Dedi Prasetyo',
                'company_name' => 'CV Berkah Jaya',
                'phone' => '083456789012',
                'address' => 'Jl. Ahmad Yani No. 5, Sampit',
                'bank_name' => 'Mandiri',
                'bank_branch' => 'Sampit',
                'account_number' => '5678901234',
                'account_holder_name' => 'Dedi Prasetyo',
            ],
        ];

        foreach ($contractors as $data) {
            Contractor::firstOrCreate(['name' => $data['name']], $data);
        }

        // Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@sas.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'position' => 'Admin',
                'site_id' => $site->id,
                'department_id' => $deptMJE->id,
            ]
        );
        $admin->assignRole('Admin');

        $asisten = User::firstOrCreate(
            ['email' => 'asisten@sas.test'],
            [
                'name' => 'Hendra Wijaya',
                'password' => Hash::make('password'),
                'position' => 'Asisten Afdeling',
                'site_id' => $site->id,
                'department_id' => $deptMJE->id,
            ]
        );
        $asisten->assignRole('Asisten Afdeling');

        $approver = User::firstOrCreate(
            ['email' => 'approver@sas.test'],
            [
                'name' => 'Surya Pratama',
                'password' => Hash::make('password'),
                'position' => 'Manager',
                'site_id' => $site->id,
                'department_id' => $deptMJE->id,
            ]
        );
        $approver->assignRole('Approver');

        $this->command->info('✅ SAS Seeder completed!');
        $this->command->info('   Users: admin@sas.test / asisten@sas.test / approver@sas.test (password: password)');
    }
}
