<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete(); // null = global
            $table->string('code', 30);
            $table->string('name');
            $table->string('type', 30); // earning, deduction, employee_contribution, employer_contribution, tax
            $table->string('calculation_method', 30)->default('fixed'); // fixed, percentage_base, percentage_gross, formula
            $table->string('base_type', 30)->nullable(); // base_salary, gross_salary, custom
            $table->boolean('is_taxable')->default(false); // soumis à impôt
            $table->boolean('is_subject_to_contributions')->default(false); // soumis à cotisations
            $table->boolean('is_visible_on_payslip')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_items');
    }
};
