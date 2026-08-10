<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('key');
                $table->text('value')->nullable();
                $table->string('group', 50)->default('general');
                $table->timestamps();
                $table->unique(['company_id', 'key']);
            });
        }

        if (!Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('code', 20);
                $table->string('name');
                $table->string('type', 30)->default('vat');
                $table->decimal('rate', 8, 4)->default(0);
                $table->string('account_number', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sequence_numbers')) {
            Schema::create('sequence_numbers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('code', 50);
                $table->string('name');
                $table->string('prefix', 20)->default('');
                $table->unsignedBigInteger('next_number')->default(1);
                $table->string('format', 60)->default('{prefix}-{year}-{number:04}');
                $table->timestamps();
                $table->unique(['company_id', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_numbers');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('settings');
    }
};
