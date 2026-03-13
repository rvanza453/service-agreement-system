<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uspk_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('uspk_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('work_type')->nullable();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('sub_department_id')->constrained('sub_departments')->onDelete('cascade');
            $table->foreignId('block_id')->constrained('blocks')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('uspk_budget_activity_id')->nullable()->constrained('uspk_budget_activities')->onDelete('set null');
            $table->decimal('estimated_value', 15, 2)->default(0);
            $table->integer('estimated_duration')->nullable()->comment('Durasi dalam hari');
            $table->string('status')->default('draft')->index();
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uspk_submissions');
    }
};
