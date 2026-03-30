<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->json('pic_user_ids')->nullable()->after('pic_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('qc_findings', function (Blueprint $table) {
            $table->dropColumn('pic_user_ids');
        });
    }
};
