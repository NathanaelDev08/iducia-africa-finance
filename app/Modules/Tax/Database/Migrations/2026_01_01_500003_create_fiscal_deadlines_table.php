<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->string('type', 50); // vat, corporate_tax, payroll_tax, etc.
            $table->string('name');
            $table->date('due_date');
            $table->string('status', 20)->default('pending'); // pending, ready, filed, paid
            $table->foreignId('related_declaration_id')->nullable(); // Lien vers vat_declarations ou autre
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['company_id', 'due_date']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_deadlines');
    }
};
