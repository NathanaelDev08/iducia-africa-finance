<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained()->restrictOnDelete();
            
            $table->string('reference', 50)->nullable();
            $table->string('name'); // ex: "TVA Août 2026"
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date')->nullable(); // Date limite de dépôt
            
            $table->decimal('total_sales_ht', 18, 2)->default(0);
            $table->decimal('total_vat_collected', 18, 2)->default(0);
            $table->decimal('total_purchases_ht', 18, 2)->default(0);
            $table->decimal('total_vat_deductible', 18, 2)->default(0);
            $table->decimal('vat_credit_previous', 18, 2)->default(0); // Crédit de TVA antérieur
            $table->decimal('vat_to_pay', 18, 2)->default(0); // TVA à décaisser
            $table->decimal('vat_credit_to_carry', 18, 2)->default(0); // Crédit à reporter
            
            $table->string('status', 20)->default('draft'); // draft, calculated, validated, filed, paid
            $table->boolean('is_locked')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('accounting_entry_id')->nullable(); // Écriture de TVA générée
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'period_id']);
            $table->index(['company_id', 'status']);
            $table->index(['due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_declarations');
    }
};
