<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('source_type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
