<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('short_name')->nullable();
                $table->string('logo_path')->nullable();
                $table->text('address')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email')->nullable();
                $table->string('rccm', 100)->nullable();
                $table->string('ncc', 100)->nullable();
                $table->string('tax_id', 100)->nullable();
                $table->string('social_id', 100)->nullable();
                $table->string('currency', 3)->default('XOF');
                $table->string('timezone')->default('Africa/Abidjan');
                $table->boolean('is_active')->default(true);
                $table->timestamp('suspended_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
