<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('hisproductos')) {
            return;
        }

        Schema::create('hisproductos', function (Blueprint $table) {
            $table->integer('HisProd_idWEB', true);
            $table->string('hisprod_familia', 5)->nullable();
            $table->string('hisprod_idprod', 10)->nullable();
            $table->string('hisprod_campo', 50)->nullable();
            $table->string('hisprod_valorant', 255)->nullable();
            $table->string('hisprod_valornvo', 255)->nullable();
            $table->string('hisprod_usuario', 50)->nullable();
            $table->string('hisprod_fecha', 30)->nullable();
            $table->integer('hisprod_sucursalorig')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hisproductos');
    }
};
