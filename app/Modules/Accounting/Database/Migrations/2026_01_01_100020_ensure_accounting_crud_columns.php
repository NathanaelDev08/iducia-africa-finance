<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fiscal_years')) {
            Schema::table('fiscal_years', function (Blueprint $t) {
                if (!Schema::hasColumn('fiscal_years','status')) $t->string('status',20)->default('open');
                if (!Schema::hasColumn('fiscal_years','name')) $t->string('name')->nullable();
            });
        }
        if (Schema::hasTable('periods')) {
            Schema::table('periods', function (Blueprint $t) {
                if (!Schema::hasColumn('periods','company_id')) $t->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                if (!Schema::hasColumn('periods','name')) $t->string('name')->nullable();
                if (!Schema::hasColumn('periods','status')) $t->string('status',20)->default('open');
            });
        }
        if (!Schema::hasTable('leaves') && Schema::hasTable('employees')) {
            Schema::create('leaves', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $t->string('leave_type',30)->default('annual');
                $t->date('start_date');
                $t->date('end_date');
                $t->integer('days_count')->default(0);
                $t->text('reason')->nullable();
                $t->string('status',20)->default('pending');
                $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('approved_at')->nullable();
                $t->timestamps();
            });
        }
    }
    public function down(): void {}
};
