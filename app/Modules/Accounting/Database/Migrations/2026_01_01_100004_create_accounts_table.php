<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chart_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->string('number', 20);
            $table->string('name');
            $table->unsignedTinyInteger('class_number');
            $table->string('type', 30);
            $table->string('category', 50)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_reconcilable')->default(false);
            $table->boolean('is_auxiliary')->default(false);
            $table->boolean('is_cash_account')->default(false);
            $table->boolean('is_bank_account')->default(false);
            $table->boolean('is_tax_account')->default(false);

            $table->unsignedBigInteger('default_tax_id')->nullable();
            $table->decimal('opening_balance', 18, 2)->default(0);

            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'chart_account_id', 'number']);
            $table->index(['company_id', 'class_number']);
            $table->index(['company_id', 'type']);
            $table->index(['parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
