<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ispo_document_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ispo_document_id')->constrained('ispo_documents')->onDelete('cascade');
            $table->foreignId('ispo_item_id')->constrained('ispo_items')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->string('audit_status')->nullable();
            $table->text('audit_notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->unique(['ispo_document_id', 'ispo_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ispo_document_entries');
    }
};
