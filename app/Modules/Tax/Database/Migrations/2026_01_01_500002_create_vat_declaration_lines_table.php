<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_declaration_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vat_declaration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('type', 30); // collected, deductible
            $table->string('description');
            $table->decimal('base_amount', 18, 2)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['vat_declaration_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_declaration_lines');
    }
};
