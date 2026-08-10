<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('matricule', 30);
            $table->string('last_name');
            $table->string('first_name');

            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('nationality')->nullable();

            $table->string('id_card_number')->nullable();
            $table->string('cnps_number')->nullable();
            $table->string('tax_id')->nullable();

            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('marital_status', 30)->nullable();
            $table->unsignedInteger('dependents_count')->default(0);

            $table->date('hire_date');
            $table->date('seniority_date')->nullable();

            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('superior_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->string('professional_category')->nullable();
            $table->string('collective_agreement')->nullable();

            $table->string('status', 20)->default('active');
            $table->date('exit_date')->nullable();
            $table->string('exit_reason')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('mobile_money')->nullable();
            $table->string('payment_method', 30)->default('bank');
            $table->string('payment_currency', 3)->default('XOF');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'matricule']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'department_id']);
            $table->index(['company_id', 'position_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
