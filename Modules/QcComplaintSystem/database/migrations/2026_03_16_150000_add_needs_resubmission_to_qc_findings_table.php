<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->boolean('needs_resubmission')->default(false)->after('completion_rejected_note');
        });
    }

    public function down(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->dropColumn('needs_resubmission');
        });
    }
};
