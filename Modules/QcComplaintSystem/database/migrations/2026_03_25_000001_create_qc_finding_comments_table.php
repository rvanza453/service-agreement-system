<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_finding_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_finding_id')->constrained('qc_findings')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_comment_id')->nullable();
            $table->foreign('parent_comment_id')
                ->references('id')
                ->on('qc_finding_comments')
                ->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->mediumText('content');
            $table->timestamps();

            // Indexes untuk performa
            $table->index(['qc_finding_id', 'parent_comment_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_finding_comments');
    }
};
