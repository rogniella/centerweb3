<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('clientes')) {
            return;
        }

        Schema::create('clientes', function (Blueprint $table) {
            $table->integer('Cli_idWEB', true);
            $table->integer('Cli_Id')->nullable();
            $table->string('Cli_ApeNom', 100)->nullable();
            $table->string('Cli_Documento', 20)->nullable();
            $table->string('Cli_Telefono', 50)->nullable();
            $table->string('Cli_Pais', 5)->nullable();
            $table->string('Cli_Calle', 100)->nullable();
            $table->string('Cli_CodRespIVA', 5)->nullable();
            $table->string('Cli_Cuil', 15)->nullable();
            $table->string('Cli_CodDocumento', 10)->nullable();
            $table->integer('Cli_sucursal')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
