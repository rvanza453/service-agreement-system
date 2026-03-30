<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ispo_documents', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ispo_documents', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreign('site_id')->references('id')->on('ispo_sites')->onDelete('cascade');
        });
    }
};
