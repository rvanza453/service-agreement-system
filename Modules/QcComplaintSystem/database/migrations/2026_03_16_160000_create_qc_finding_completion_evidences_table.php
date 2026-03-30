<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_finding_completion_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_finding_id')->constrained('qc_findings')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('qc_finding_id', 'qc_finding_completion_evidences_finding_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_finding_completion_evidences');
    }
};