<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // vat, tax_on_salaries, corporate_income_tax
            $table->string('reference', 50)->nullable();
            $table->string('period', 20); // ex: 2026-08
            $table->date('due_date');
            $table->string('status', 20)->default('pending'); // pending, filed, paid, late
            $table->decimal('base_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('penalty_amount', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('filed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'type', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_declarations');
    }
};
