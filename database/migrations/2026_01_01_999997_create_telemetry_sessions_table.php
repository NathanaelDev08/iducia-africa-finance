<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('telemetry_sessions')) {
            Schema::create('telemetry_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('session_id', 64)->index();
                $table->string('ip_address', 45)->nullable();
                $table->string('country', 2)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('device_type', 20)->nullable(); // desktop, mobile, tablet
                $table->string('browser', 50)->nullable();
                $table->string('os', 50)->nullable();
                $table->timestamp('started_at')->index();
                $table->timestamp('ended_at')->nullable();
                $table->integer('duration_seconds')->nullable();
                $table->integer('pages_viewed')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('telemetry_events')) {
            Schema::create('telemetry_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_name', 100)->index();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('session_id', 64)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();
            });
        }

        // Ajouter des colonnes aux users pour le tracking
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'login_count')) $table->integer('login_count')->default(0);
                if (!Schema::hasColumn('users', 'first_login_at')) $table->timestamp('first_login_at')->nullable();
                if (!Schema::hasColumn('users', 'last_login_at')) $table->timestamp('last_login_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_events');
        Schema::dropIfExists('telemetry_sessions');
    }
};
