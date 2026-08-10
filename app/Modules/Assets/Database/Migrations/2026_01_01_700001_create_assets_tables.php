<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->string('code', 20)->unique();
                $t->string('name');
                $t->date('acquisition_date');
                $t->decimal('acquisition_cost', 18, 2)->default(0);
                $t->decimal('residual_value', 18, 2)->default(0);
                $t->integer('useful_life_months')->default(60);
                $t->string('depreciation_method', 20)->default('linear');
                $t->string('account_asset', 20)->nullable();   // 21x
                $t->string('account_depreciation', 20)->nullable(); // 28x
                $t->string('account_expense', 20)->nullable(); // 68x
                $t->string('status', 20)->default('active'); // active, disposed
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('asset_depreciations')) {
            Schema::create('asset_depreciations', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('asset_id')->constrained()->cascadeOnDelete();
                $t->string('period', 7); // YYYY-MM
                $t->date('depreciation_date');
                $t->decimal('amount', 18, 2)->default(0);
                $t->decimal('accumulated', 18, 2)->default(0);
                $t->decimal('net_book_value', 18, 2)->default(0);
                $t->unsignedBigInteger('accounting_entry_id')->nullable();
                $t->string('status', 20)->default('draft');
                $t->timestamps();
                $t->unique(['asset_id', 'period']);
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('assets');
    }
};
