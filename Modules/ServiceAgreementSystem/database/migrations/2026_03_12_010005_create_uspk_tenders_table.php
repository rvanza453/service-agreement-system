<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uspk_tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uspk_submission_id')->constrained('uspk_submissions')->onDelete('cascade');
            $table->foreignId('contractor_id')->constrained('contractors')->onDelete('cascade');
            $table->decimal('tender_value', 15, 2)->default(0);
            $table->integer('tender_duration')->nullable()->comment('Durasi dalam hari');
            $table->text('description')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uspk_tenders');
    }
};
