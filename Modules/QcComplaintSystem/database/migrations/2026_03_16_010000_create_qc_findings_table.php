<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\QcComplaintSystem\Models\QcFinding;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('source_type')->default(QcFinding::SOURCE_QC_SITE)->index();

            // Location hierarchy: department -> afdeling(sub_department) -> block
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('sub_department_id')->constrained('sub_departments')->cascadeOnDelete();
            $table->foreignId('block_id')->constrained('blocks')->cascadeOnDelete();
            $table->string('location')->nullable()->comment('Detail lokasi tambahan opsional');

            $table->string('urgency')->default(QcFinding::URGENCY_MEDIUM)->index();
            $table->string('status')->default(QcFinding::STATUS_OPEN)->index();

            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('completion_note')->nullable();
            $table->string('completion_photo_path')->nullable();
            $table->foreignId('completion_submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completion_submitted_at')->nullable();

            $table->foreignId('completion_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completion_approved_at')->nullable();
            $table->text('completion_approval_note')->nullable();
            $table->text('completion_rejected_note')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Indexes for common dashboard/filter queries
            $table->index(['department_id', 'sub_department_id', 'block_id'], 'qc_findings_location_idx');
            $table->index(['status', 'urgency'], 'qc_findings_status_urgency_idx');
            $table->index(['pic_user_id', 'status'], 'qc_findings_pic_status_idx');
            $table->index(['created_at', 'status'], 'qc_findings_created_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_findings');
    }
};
