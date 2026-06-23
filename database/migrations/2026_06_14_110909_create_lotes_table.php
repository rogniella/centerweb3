<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lotes')) {
            return;
        }

        Schema::create('lotes', function (Blueprint $table) {
            $table->integer('Lot_Id', true);
            $table->integer('Lot_IdProv')->nullable();
            $table->string('Lot_Operacion', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
