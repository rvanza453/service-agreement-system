<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->date('finding_date')->nullable()->after('finding_number');
            $table->boolean('is_long_term_case')->default(false)->after('needs_resubmission');
            $table->date('target_resolution_date')->nullable()->after('is_long_term_case');
            $table->text('follow_up_plan')->nullable()->after('target_resolution_date');
        });
    }

    public function down(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->dropColumn([
                'finding_date',
                'is_long_term_case',
                'target_resolution_date',
                'follow_up_plan',
            ]);
        });
    }
};
