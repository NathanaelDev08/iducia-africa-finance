<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pay_item_id')->constrained()->restrictOnDelete();
            $table->string('code', 30);
            $table->string('label');
            $table->string('type', 30);
            $table->decimal('base_amount', 18, 2)->default(0);
            $table->decimal('rate', 10, 4)->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('employer_amount', 18, 2)->default(0); // part patronale
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['payslip_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_lines');
    }
};
