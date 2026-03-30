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
        Schema::create('uspk_approval_schema_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_id')->constrained('uspk_approval_schemas')->cascadeOnDelete();
            $table->integer('level');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['schema_id', 'level'], 'schema_step_level_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uspk_approval_schema_steps');
    }
};
