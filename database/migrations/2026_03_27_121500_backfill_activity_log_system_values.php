<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs') || !Schema::hasColumn('activity_logs', 'system')) {
            return;
        }

        DB::table('activity_logs')
            ->whereNull('system')
            ->where('subject_type', 'like', 'Modules\\\\QcComplaintSystem\\\\%')
            ->update(['system' => 'QC']);

        DB::table('activity_logs')
            ->whereNull('system')
            ->where('subject_type', 'like', 'Modules\\\\ServiceAgreementSystem\\\\%')
            ->update(['system' => 'SAS']);

        DB::table('activity_logs')
            ->whereNull('system')
            ->where('subject_type', 'like', 'Modules\\\\SystemISPO\\\\%')
            ->update(['system' => 'ISPO']);

        DB::table('activity_logs')
            ->whereNull('system')
            ->where('subject_type', 'like', 'Modules\\\\PrSystem\\\\%')
            ->update(['system' => 'PR']);

        // Existing historical logs in this project were PR-centric before cross-module logging.
        DB::table('activity_logs')
            ->whereNull('system')
            ->update(['system' => 'PR']);
    }

    public function down(): void
    {
        // Intentionally left blank to preserve historical audit labels.
    }
};
