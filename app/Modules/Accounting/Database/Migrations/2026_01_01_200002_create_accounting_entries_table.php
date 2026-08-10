<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_id')->constrained()->restrictOnDelete();
            $table->foreignId('period_id')->constrained()->restrictOnDelete();

            $table->string('entry_number', 30)->nullable();
            $table->string('reference', 100)->nullable();
            $table->date('entry_date');
            $table->text('description');

            $table->string('status', 20)->default('draft');
            $table->boolean('is_locked')->default(false);

            $table->foreignId('reversal_of_id')->nullable()->constrained('accounting_entries')->nullOnDelete();
            $table->foreignId('reversed_by_id')->nullable()->constrained('accounting_entries')->nullOnDelete();

            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('attachment_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'journal_id', 'entry_number']);
            $table->index(['company_id', 'entry_date']);
            $table->index(['company_id', 'status']);
            $table->index(['period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entries');
    }
};
