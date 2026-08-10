<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_contributions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('organism', 50)->default('CNPS'); // CNPS, autres
            $table->string('employee_account_code', 20)->nullable(); // compte comptable part salariale
            $table->string('employer_account_code', 20)->nullable(); // compte comptable part patronale
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('social_contribution_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_contribution_id')->constrained()->cascadeOnDelete();
            $table->decimal('employee_rate', 8, 4)->default(0); // taux part salariale %
            $table->decimal('employer_rate', 8, 4)->default(0); // taux part patronale %
            $table->decimal('ceiling', 18, 2)->nullable(); // plafond de cotisation
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['social_contribution_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_contribution_rates');
        Schema::dropIfExists('social_contributions');
    }
};
