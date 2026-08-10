<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('contract_type_id')->constrained('contract_types')->restrictOnDelete();

            $table->string('contract_number')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('trial_period_end_date')->nullable();

            $table->decimal('working_hours_per_week', 5, 2)->nullable();
            $table->decimal('base_salary', 18, 2)->default(0);

            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
