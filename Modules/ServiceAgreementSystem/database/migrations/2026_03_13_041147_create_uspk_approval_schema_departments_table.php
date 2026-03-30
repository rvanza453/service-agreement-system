<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uspk_approval_schema_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_id')->constrained('uspk_approval_schemas')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['schema_id', 'department_id'], 'schema_dept_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uspk_approval_schema_departments');
    }
};
