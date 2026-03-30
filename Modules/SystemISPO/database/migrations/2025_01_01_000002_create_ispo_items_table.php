<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ispo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('ispo_items')->onDelete('cascade');
            $table->string('type');
            $table->string('code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ispo_items');
    }
};
