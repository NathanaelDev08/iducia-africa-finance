<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('pay_run_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('pay_item_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('quantity', 10, 2)->nullable(); // ex: nombre d'heures
            $table->date('effective_date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_variables');
    }
};
