<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uspk_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uspk_submission_id')->constrained('uspk_submissions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('level');
            $table->string('role_name');
            $table->string('status')->default('pending')->index();
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['uspk_submission_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uspk_approvals');
    }
};
