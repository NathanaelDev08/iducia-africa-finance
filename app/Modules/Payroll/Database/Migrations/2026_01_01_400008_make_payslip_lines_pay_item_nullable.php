<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payslip_lines')) {
            try {
                DB::statement('ALTER TABLE payslip_lines ALTER COLUMN pay_item_id DROP NOT NULL;');
            } catch (\Throwable $e) {
                // Column already nullable or not present
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payslip_lines')) {
            try {
                DB::statement('ALTER TABLE payslip_lines ALTER COLUMN pay_item_id SET NOT NULL;');
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
