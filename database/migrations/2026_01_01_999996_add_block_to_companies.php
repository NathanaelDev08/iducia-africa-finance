<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function ($table) {
                if (!Schema::hasColumn('companies', 'is_blocked')) $table->boolean('is_blocked')->default(false)->index();
                if (!Schema::hasColumn('companies', 'blocked_at')) $table->timestamp('blocked_at')->nullable();
            });
        }
    }
    public function down(): void {}
};
