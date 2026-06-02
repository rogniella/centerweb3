<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cierre', function (Blueprint $table) {
            $table->decimal('Cie_retiro_d', 19, 4)->nullable()->after('Cie_queda_r_final');
            $table->decimal('Cie_queda_d', 19, 4)->nullable()->after('Cie_retiro_d');
            $table->decimal('Cie_retiro_d_final', 19, 4)->nullable()->after('Cie_queda_d');
            $table->decimal('Cie_queda_d_final', 19, 4)->nullable()->after('Cie_retiro_d_final');
            $table->decimal('Cie_ajuste_d', 19, 4)->nullable()->after('Cie_queda_d_final');
            $table->string('Cie_ajuste_d_motivo', 40)->nullable()->after('Cie_ajuste_d');
        });
    }

    public function down(): void
    {
        Schema::table('cierre', function (Blueprint $table) {
            $table->dropColumn([
                'Cie_retiro_d',
                'Cie_queda_d',
                'Cie_retiro_d_final',
                'Cie_queda_d_final',
                'Cie_ajuste_d',
                'Cie_ajuste_d_motivo',
            ]);
        });
    }
};
