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
        Schema::dropIfExists('uspk_approval_configs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('uspk_approval_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('role_name');
            $table->integer('level');
            $table->decimal('min_value', 15, 2)->nullable();
            $table->timestamps();
        });
    }
};
