<?php

namespace Tests\Feature\Actions;

use App\Actions\Movimientos\ListarAuditoriaAction;
use App\Actions\Movimientos\ListarMovimientosAction;
use App\Models\producto;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MovimientosActionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('moviproductos')) {
            Schema::create('moviproductos', function ($table) {
                $table->id('mov_idWEB');
                $table->string('mov_familia', 10)->nullable();
                $table->string('mov_idprod', 20)->nullable();
                $table->dateTime('mov_fecmov')->nullable();
                $table->decimal('mov_cantidad', 12, 2)->default(0);
                $table->decimal('mov_precio', 12, 2)->default(0);
                $table->string('mov_operacion', 10)->nullable();
                $table->string('mov_motivo', 255)->nullable();
                $table->string('mov_descripcion', 255)->nullable();
                $table->integer('mov_sucursal')->default(0);
            });
        }
    }

    public function test_listar_movimientos_returns_json(): void
    {
        $producto = producto::factory()->create();
        $response = app(ListarMovimientosAction::class)($producto->Prod_Familia, $producto->Prod_Id);

        $data = $response->getData(true);
        $this->assertArrayHasKey('results', $data);
    }

    public function test_listar_movimientos_returns_empty_for_nonexistent(): void
    {
        $response = app(ListarMovimientosAction::class)('REC', '99999');
        $data = $response->getData(true);
        $this->assertEmpty($data['results']);
    }

    public function test_listar_auditoria_returns_json(): void
    {
        $producto = producto::factory()->create();
        $response = app(ListarAuditoriaAction::class)($producto->Prod_Familia, $producto->Prod_Id);

        $data = $response->getData(true);
        $this->assertArrayHasKey('results', $data);
    }

    public function test_listar_auditoria_returns_empty_for_nonexistent(): void
    {
        $response = app(ListarAuditoriaAction::class)('REC', '99999');
        $data = $response->getData(true);
        $this->assertEmpty($data['results']);
    }
}
