<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('uspk_approvals', function (Blueprint $table) {
            $table->foreignId('schema_id')
                  ->after('uspk_submission_id')
                  ->nullable()
                  ->constrained('uspk_approval_schemas')
                  ->nullOnDelete();
                  
            $table->dropColumn('role_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uspk_approvals', function (Blueprint $table) {
            $table->dropForeign(['schema_id']);
            $table->dropColumn('schema_id');
            $table->string('role_name')->after('level')->default('Approver');
        });
    }
};
