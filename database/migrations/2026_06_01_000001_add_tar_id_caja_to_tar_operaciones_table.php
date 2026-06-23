<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('tar_operaciones')) {
            return;
        }

        Schema::table('tar_operaciones', function (Blueprint $table) {
            $table->integer('tar_idCaja')->nullable()->after('observacion')->comment('FK a caja.Caj_IdWEB');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tar_operaciones')) {
            return;
        }

        Schema::table('tar_operaciones', function (Blueprint $table) {
            $table->dropColumn('tar_idCaja');
        });
    }
};
