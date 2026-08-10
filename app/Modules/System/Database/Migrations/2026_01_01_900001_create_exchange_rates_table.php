<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $t->string('currency_code', 10);      // EUR, USD...
                $t->string('currency_name')->nullable();
                $t->decimal('rate_to_base', 18, 6)->default(1); // 1 unité -> XOF
                $t->date('effective_from');
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('exchange_rates'); }
};
