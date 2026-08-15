<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            if (!Schema::hasColumn('payslips', 'taxable_income')) {
                $table->decimal('taxable_income', 18, 2)->default(0)->after('total_deductions');
            }
            if (!Schema::hasColumn('payslips', 'income_tax')) {
                $table->decimal('income_tax', 18, 2)->default(0)->after('taxable_income');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['taxable_income', 'income_tax']);
        });
    }
};
