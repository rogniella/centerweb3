<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('proveedores')) {
            return;
        }

        Schema::create('proveedores', function (Blueprint $table) {
            $table->integer('Prov_id')->primary();
            $table->string('Prov_RazSocial', 100)->nullable();
            $table->string('Prov_NomFant', 100)->nullable();
            $table->string('Prov_Telefono', 50)->nullable();
            $table->string('Prov_Calle', 100)->nullable();
            $table->string('Prov_EMail', 100)->nullable();
            $table->string('Prov_Cuit', 20)->nullable();
            $table->string('Prov_CtaCon', 10)->nullable();
            $table->string('Prov_TipoProv', 10)->nullable();
            $table->string('Prov_Observ', 255)->nullable();
            $table->string('Prov_FormaPago', 50)->nullable();
            $table->string('Prov_FecUltMan', 30)->nullable();
            $table->string('Prov_FecAlta', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
