<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'perfil_id')) {
                $table->string('perfil_id')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'apellidonombre')) {
                $table->string('apellidonombre')->nullable()->after('perfil_id');
            }
            if (! Schema::hasColumn('users', 'id_entidadrelacionada')) {
                $table->integer('id_entidadrelacionada')->default(0)->after('apellidonombre');
            }
            if (! Schema::hasColumn('users', 'sucursal')) {
                $table->integer('sucursal')->nullable()->after('id_entidadrelacionada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['perfil_id', 'apellidonombre', 'id_entidadrelacionada', 'sucursal'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
