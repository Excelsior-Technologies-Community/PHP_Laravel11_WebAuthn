<?php
// database/migrations/2026_05_15_000007_add_missing_columns_to_login_activities.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_activities', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('login_activities', 'device_type')) {
                $table->string('device_type')->nullable()->after('browser');
            }
            
            if (!Schema::hasColumn('login_activities', 'device_name')) {
                $table->string('device_name')->nullable()->after('device_type');
            }
            
            if (!Schema::hasColumn('login_activities', 'country')) {
                $table->string('country')->nullable()->after('login_method');
            }
            
            if (!Schema::hasColumn('login_activities', 'city')) {
                $table->string('city')->nullable()->after('country');
            }
            
            if (!Schema::hasColumn('login_activities', 'is_trusted')) {
                $table->boolean('is_trusted')->default(false)->after('device_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('login_activities', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'device_name', 'country', 'city', 'is_trusted']);
        });
    }
};