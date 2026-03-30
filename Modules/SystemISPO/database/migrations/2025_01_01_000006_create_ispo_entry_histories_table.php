<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ispo_entry_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ispo_document_entry_id')->constrained('ispo_document_entries')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('role')->nullable();
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->string('audit_status')->nullable();
            $table->text('audit_notes')->nullable();
            $table->json('attachments_snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ispo_entry_histories');
    }
};
