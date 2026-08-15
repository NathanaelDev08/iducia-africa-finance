<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('stock_items')) {
            Schema::create('stock_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->string('code', 30);
                $t->string('name');
                $t->string('category', 100)->nullable();
                $t->string('unit', 20)->default('unité');
                $t->decimal('quantity_on_hand', 18, 2)->default(0);
                $t->decimal('unit_cost', 18, 2)->default(0);
                $t->decimal('reorder_threshold', 18, 2)->default(0);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
                $t->unique(['company_id', 'code']);
            });
        }

        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnDelete();
                $t->string('type', 20); // in, out, adjustment
                $t->decimal('quantity', 18, 2);
                $t->decimal('unit_cost', 18, 2)->nullable();
                $t->string('reference', 100)->nullable();
                $t->text('note')->nullable();
                $t->date('movement_date');
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_items');
    }
};
