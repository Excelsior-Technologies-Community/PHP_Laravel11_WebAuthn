<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'webauthn_required')) {
                $table->boolean('webauthn_required')->default(false)->after('password');
            }

            if (!Schema::hasColumn('users', 'email_verified')) {
                $table->boolean('email_verified')->default(false)->after('webauthn_required');
            }

            if (!Schema::hasColumn('users', 'last_passkey_login')) {
                $table->timestamp('last_passkey_login')->nullable()->after('email_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['webauthn_required', 'email_verified', 'last_passkey_login']);
        });
    }
};