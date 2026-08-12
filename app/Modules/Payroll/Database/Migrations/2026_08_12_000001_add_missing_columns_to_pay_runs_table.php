<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pay_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('pay_runs', 'reference')) {
                $table->string('reference', 50)->nullable()->after('name');
            }
            if (! Schema::hasColumn('pay_runs', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('status');
            }
            if (! Schema::hasColumn('pay_runs', 'accounting_entry_id')) {
                $table->foreignId('accounting_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete()->after('approved_at');
            }
            if (! Schema::hasColumn('pay_runs', 'locked_by')) {
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete()->after('accounting_entry_id');
            }
            if (! Schema::hasColumn('pay_runs', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('locked_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pay_runs', function (Blueprint $table) {
            if (Schema::hasColumn('pay_runs', 'locked_at')) {
                $table->dropColumn('locked_at');
            }
            if (Schema::hasColumn('pay_runs', 'locked_by')) {
                $table->dropConstrainedForeignId('locked_by');
            }
            if (Schema::hasColumn('pay_runs', 'accounting_entry_id')) {
                $table->dropConstrainedForeignId('accounting_entry_id');
            }
            if (Schema::hasColumn('pay_runs', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
            if (Schema::hasColumn('pay_runs', 'reference')) {
                $table->dropColumn('reference');
            }
        });
    }
};
