<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->string('code', 20)->unique();
                $t->string('name');
                $t->string('contact_name')->nullable();
                $t->string('email')->nullable();
                $t->string('phone')->nullable();
                $t->text('address')->nullable();
                $t->string('tax_number')->nullable();
                $t->string('account_number', 20)->nullable();
                $t->string('payment_terms')->nullable();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $t->string('reference')->unique();
                $t->date('order_date');
                $t->date('expected_date')->nullable();
                $t->string('status', 20)->default('draft');
                $t->decimal('total_ht', 18, 2)->default(0);
                $t->decimal('total_tax', 18, 2)->default(0);
                $t->decimal('total_ttc', 18, 2)->default(0);
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
                $t->string('description');
                $t->decimal('quantity', 14, 3)->default(1);
                $t->decimal('unit_price', 18, 2)->default(0);
                $t->decimal('tax_rate', 5, 2)->default(18);
                $t->decimal('total_ht', 18, 2)->default(0);
                $t->decimal('total_tax', 18, 2)->default(0);
                $t->decimal('total_ttc', 18, 2)->default(0);
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('purchase_invoices')) {
            Schema::create('purchase_invoices', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $t->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
                $t->string('reference')->unique();
                $t->string('supplier_invoice_number')->nullable();
                $t->date('invoice_date');
                $t->date('due_date')->nullable();
                $t->string('status', 20)->default('draft');
                $t->decimal('total_ht', 18, 2)->default(0);
                $t->decimal('total_tax', 18, 2)->default(0);
                $t->decimal('total_ttc', 18, 2)->default(0);
                $t->decimal('amount_paid', 18, 2)->default(0);
                $t->unsignedBigInteger('accounting_entry_id')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('purchase_invoice_items')) {
            Schema::create('purchase_invoice_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
                $t->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
                $t->string('description');
                $t->decimal('quantity', 14, 3)->default(1);
                $t->decimal('unit_price', 18, 2)->default(0);
                $t->decimal('tax_rate', 5, 2)->default(18);
                $t->decimal('total_ht', 18, 2)->default(0);
                $t->decimal('total_tax', 18, 2)->default(0);
                $t->decimal('total_ttc', 18, 2)->default(0);
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('supplier_payments')) {
            Schema::create('supplier_payments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $t->foreignId('purchase_invoice_id')->nullable()->constrained()->nullOnDelete();
                $t->string('reference')->unique();
                $t->date('payment_date');
                $t->string('payment_method', 20)->default('bank');
                $t->decimal('amount', 18, 2)->default(0);
                $t->unsignedBigInteger('accounting_entry_id')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
