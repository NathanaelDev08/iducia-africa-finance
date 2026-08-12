<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'first_login_at')) {
                $table->timestamp('first_login_at')->nullable()->after('must_change_password');
            }
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('first_login_at');
            }
            if (!Schema::hasColumn('users', 'temp_password_token')) {
                $table->string('temp_password_token', 64)->nullable()->after('password_changed_at');
            }
            if (!Schema::hasColumn('users', 'temp_password_expires_at')) {
                $table->timestamp('temp_password_expires_at')->nullable()->after('temp_password_token');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('temp_password_expires_at');
            }
            if (!Schema::hasColumn('users', 'invited_by_type')) {
                $table->string('invited_by_type')->nullable();
            }
            if (!Schema::hasColumn('users', 'invited_by')) {
                $table->unsignedBigInteger('invited_by')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'must_change_password', 'first_login_at', 'password_changed_at',
                'temp_password_token', 'temp_password_expires_at', 'is_active',
                'invited_by_type', 'invited_by',
            ]);
        });
    }
};
