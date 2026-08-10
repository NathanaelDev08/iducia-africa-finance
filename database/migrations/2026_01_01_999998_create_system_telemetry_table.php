<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('system_telemetry')) {
            Schema::create('system_telemetry', function (Blueprint $table) {
                $table->id();
                $table->string('install_id', 64)->index();
                $table->json('payload');
                $table->timestamp('recorded_at')->index();
                $table->timestamps();
            });
        }
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_seen_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_telemetry');
    }
};
