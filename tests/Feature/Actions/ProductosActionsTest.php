<?php

namespace Tests\Feature\Actions;

use App\Actions\Productos\GenerarNuevoCodigoAction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductosActionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('familias')) {
            Schema::create('familias', function ($table) {
                $table->string('Flia_Id', 10)->primary();
                $table->string('Flia_Descripcion', 100)->nullable();
                $table->integer('Flia_MaxId')->default(0);
                $table->string('Flia_estado', 1)->default('A');
            });
        }
    }

    public function test_generar_nuevo_codigo_returns_code(): void
    {
        DB::table('familias')->insert([
            'Flia_Id' => 'REC',
            'Flia_Descripcion' => 'Recetados',
            'Flia_MaxId' => 100,
            'Flia_estado' => 'A',
        ]);

        $response = app(GenerarNuevoCodigoAction::class)('REC');
        $data = $response->getData(true);

        $this->assertArrayHasKey('NvoCodigo', $data);
        $this->assertNotEmpty($data['NvoCodigo']);
    }
}
