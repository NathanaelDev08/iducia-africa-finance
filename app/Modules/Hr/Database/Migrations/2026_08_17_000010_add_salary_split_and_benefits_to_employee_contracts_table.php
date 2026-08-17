<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            // Découpage du salaire de base : salaire catégoriel (grille conventionnelle) + sursalaire (complément).
            $table->decimal('salaire_categoriel', 18, 2)->nullable()->after('base_salary');
            $table->decimal('sursalaire', 18, 2)->nullable()->after('salaire_categoriel');

            // Couverture Maladie Universelle (CMU) : activable par contrat, coût estimé calculé côté UI.
            $table->boolean('has_cmu')->default(false)->after('sursalaire');

            // Cotisation CNPS : normalement obligatoire, mais laisse la possibilité de la décocher (ex. régime particulier).
            $table->boolean('has_cnps')->default(true)->after('has_cmu');
        });
    }

    public function down(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->dropColumn(['salaire_categoriel', 'sursalaire', 'has_cmu', 'has_cnps']);
        });
    }
};
