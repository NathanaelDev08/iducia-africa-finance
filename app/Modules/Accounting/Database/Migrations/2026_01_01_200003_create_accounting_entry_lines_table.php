<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entry_id')->constrained('accounting_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();

            $table->text('description')->nullable();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);

            $table->string('third_party_type', 30)->nullable();
            $table->unsignedBigInteger('third_party_id')->nullable();

            $table->foreignId('lettering_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'entry_id']);
            $table->index(['company_id', 'account_id']);
            $table->index(['lettering_id']);
            $table->index(['third_party_type', 'third_party_id']);
        });

        try {
            DB::statement('ALTER TABLE accounting_entry_lines ADD CONSTRAINT chk_debit_credit CHECK (debit >= 0 AND credit >= 0 AND (debit > 0 OR credit > 0) AND NOT (debit > 0 AND credit > 0));');
        } catch (\Exception $e) {
            // Ignoré si non supporté
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entry_lines');
    }
};
