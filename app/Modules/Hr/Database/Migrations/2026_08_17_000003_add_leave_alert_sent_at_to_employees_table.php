<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'leave_alert_sent_at')) {
                $table->timestamp('leave_alert_sent_at')->nullable()->after('exit_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'leave_alert_sent_at')) {
                $table->dropColumn('leave_alert_sent_at');
            }
        });
    }
};
