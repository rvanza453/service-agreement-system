<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action')->index();
                $table->string('description');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('system', 32)->nullable()->index();
                $table->string('route_name')->nullable()->index();
                $table->string('http_method', 10)->nullable();
                $table->text('url')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index(['subject_type', 'subject_id']);
                $table->index('created_at');
            });

            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'system')) {
                $table->string('system', 32)->nullable()->after('subject_id')->index();
            }

            if (!Schema::hasColumn('activity_logs', 'route_name')) {
                $table->string('route_name')->nullable()->after('system')->index();
            }

            if (!Schema::hasColumn('activity_logs', 'http_method')) {
                $table->string('http_method', 10)->nullable()->after('route_name');
            }

            if (!Schema::hasColumn('activity_logs', 'url')) {
                $table->text('url')->nullable()->after('http_method');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            $dropCandidates = [];

            if (Schema::hasColumn('activity_logs', 'system')) {
                $dropCandidates[] = 'system';
            }

            if (Schema::hasColumn('activity_logs', 'route_name')) {
                $dropCandidates[] = 'route_name';
            }

            if (Schema::hasColumn('activity_logs', 'http_method')) {
                $dropCandidates[] = 'http_method';
            }

            if (Schema::hasColumn('activity_logs', 'url')) {
                $dropCandidates[] = 'url';
            }

            if (!empty($dropCandidates)) {
                $table->dropColumn($dropCandidates);
            }
        });
    }
};
