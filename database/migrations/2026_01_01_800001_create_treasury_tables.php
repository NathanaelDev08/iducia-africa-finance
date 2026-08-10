<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('bank_statements')) {
            Schema::create('bank_statements', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('account_id')->nullable()->constrained()->nullOnDelete(); // 521
                $t->date('period_start');
                $t->date('period_end');
                $t->decimal('opening_balance', 18, 2)->default(0);
                $t->decimal('closing_balance', 18, 2)->default(0);
                $t->string('status', 20)->default('draft'); // draft, reconciled
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('bank_statement_lines')) {
            Schema::create('bank_statement_lines', function (Blueprint $t) {
                $t->id();
                $t->foreignId('bank_statement_id')->constrained()->cascadeOnDelete();
                $t->date('transaction_date');
                $t->string('reference')->nullable();
                $t->string('description')->nullable();
                $t->decimal('debit', 18, 2)->default(0);
                $t->decimal('credit', 18, 2)->default(0);
                $t->unsignedBigInteger('matched_journal_item_id')->nullable();
                $t->string('status', 20)->default('unmatched');
                $t->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
    }
};
