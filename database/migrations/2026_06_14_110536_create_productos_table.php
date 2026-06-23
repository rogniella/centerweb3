<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('productos')) {
            return;
        }

        Schema::create('productos', function (Blueprint $table) {
            $table->integer('Prod_idWEB', true);
            $table->string('Prod_Familia', 5)->nullable();
            $table->string('Prod_Id', 10)->nullable();
            $table->string('Prod_Categoria', 50)->nullable();
            $table->string('Prod_Descripcion', 255)->nullable();
            $table->decimal('Prod_Precio', 19, 4)->nullable();
            $table->decimal('Prod_Precio2', 19, 4)->nullable();
            $table->decimal('Prod_Costo', 19, 4)->nullable();
            $table->string('Prod_Estado', 5)->nullable();
            $table->string('Prod_Marca', 50)->nullable();
            $table->string('Prod_CodBarra', 30)->nullable();
            $table->decimal('Prod_TasaIva', 5, 2)->nullable();
            $table->string('Prod_UsuUltMan', 50)->nullable();
            $table->string('Prod_FecUltMan', 30)->nullable();
            $table->string('Prod_FecAlta', 30)->nullable();
            $table->string('Prod_UsuAlta', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
