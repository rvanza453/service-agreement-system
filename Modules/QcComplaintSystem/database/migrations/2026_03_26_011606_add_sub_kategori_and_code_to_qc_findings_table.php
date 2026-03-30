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
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->string('sub_kategori')->nullable()->after('kategori');
            $table->string('kategori_code')->nullable()->after('sub_kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->dropColumn(['sub_kategori', 'kategori_code']);
        });
    }
};
