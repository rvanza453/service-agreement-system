<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ispo_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('ispo_sites')->onDelete('cascade');
            $table->integer('year');
            $table->string('document_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['site_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ispo_documents');
    }
};
