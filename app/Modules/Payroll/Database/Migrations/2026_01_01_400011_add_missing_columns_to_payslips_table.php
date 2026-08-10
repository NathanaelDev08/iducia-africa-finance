<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            if (!Schema::hasColumn('payslips', 'employer_contributions')) {
                $table->decimal('employer_contributions', 18, 2)->default(0)->after('net_salary');
            }
            if (!Schema::hasColumn('payslips', 'total_earnings')) {
                $table->decimal('total_earnings', 18, 2)->default(0)->after('gross_salary');
            }
            if (!Schema::hasColumn('payslips', 'total_deductions')) {
                $table->decimal('total_deductions', 18, 2)->default(0)->after('total_earnings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['employer_contributions', 'total_earnings', 'total_deductions']);
        });
    }
};
