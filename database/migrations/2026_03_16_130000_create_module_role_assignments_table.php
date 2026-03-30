<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('module_key', 60);
            $table->string('role_name', 120);
            $table->timestamps();

            $table->unique(['user_id', 'module_key'], 'module_role_assignments_user_module_unique');
            $table->index('module_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_role_assignments');
    }
};
