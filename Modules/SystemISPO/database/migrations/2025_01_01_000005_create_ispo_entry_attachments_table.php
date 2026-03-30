<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ispo_entry_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ispo_document_entry_id')->constrained('ispo_document_entries')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ispo_entry_attachments');
    }
};
