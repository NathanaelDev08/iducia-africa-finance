<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_item_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pay_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate', 10, 4)->nullable(); // taux en %
            $table->decimal('fixed_amount', 18, 2)->nullable(); // montant fixe
            $table->decimal('ceiling', 18, 2)->nullable(); // plafond
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['pay_item_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_item_rates');
    }
};
