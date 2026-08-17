<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('matricule');
            }
            if (! Schema::hasColumn('employees', 'spouse_name')) {
                $table->string('spouse_name')->nullable()->after('dependents_count');
            }
            if (! Schema::hasColumn('employees', 'spouse_profession')) {
                $table->string('spouse_profession')->nullable()->after('spouse_name');
            }
            if (! Schema::hasColumn('employees', 'spouse_employer')) {
                $table->string('spouse_employer')->nullable()->after('spouse_profession');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'spouse_employer')) {
                $table->dropColumn('spouse_employer');
            }
            if (Schema::hasColumn('employees', 'spouse_profession')) {
                $table->dropColumn('spouse_profession');
            }
            if (Schema::hasColumn('employees', 'spouse_name')) {
                $table->dropColumn('spouse_name');
            }
            if (Schema::hasColumn('employees', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};
