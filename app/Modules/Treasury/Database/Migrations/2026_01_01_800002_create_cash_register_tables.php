<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cash_registers')) {
            Schema::create('cash_registers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('opening_balance', 18, 2)->default(0);
                $table->decimal('closing_balance', 18, 2)->default(0);
                $table->string('status', 20)->default('draft');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cash_transactions')) {
            Schema::create('cash_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
                $table->date('transaction_date');
                $table->string('reference')->nullable();
                $table->string('description')->nullable();
                $table->string('type', 20)->default('in');
                $table->decimal('amount', 18, 2)->default(0);
                $table->string('status', 20)->default('recorded');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('cash_registers');
    }
};
