<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_finding_approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_finding_id')->constrained('qc_findings')->cascadeOnDelete();
            $table->unsignedInteger('level');
            $table->foreignId('approver_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->text('note')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['qc_finding_id', 'level'], 'qc_finding_approval_steps_finding_level_unique');
            $table->index(['approver_user_id', 'status'], 'qc_finding_approval_steps_approver_status_idx');
            $table->index(['qc_finding_id', 'status'], 'qc_finding_approval_steps_finding_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_finding_approval_steps');
    }
};
