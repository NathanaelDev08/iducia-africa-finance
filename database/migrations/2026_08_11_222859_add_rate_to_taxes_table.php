<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('taxes', 'rate')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->decimal('rate', 8, 4)->default(0)->after('type');
            });

            if (Schema::hasTable('tax_rates')) {
                $rates = DB::table('tax_rates')
                    ->where('is_active', true)
                    ->orderByDesc('effective_from')
                    ->get()
                    ->unique('tax_id');

                foreach ($rates as $rate) {
                    DB::table('taxes')
                        ->where('id', $rate->tax_id)
                        ->update(['rate' => $rate->rate]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('taxes', 'rate')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->dropColumn('rate');
            });
        }
    }
};
