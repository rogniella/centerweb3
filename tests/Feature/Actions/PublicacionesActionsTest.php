<?php

namespace Tests\Feature\Actions;

use App\Actions\Publicaciones\CrearPublicacionAction;
use App\Actions\Publicaciones\RegistrarVentaOnlineAction;
use App\Models\producto;
use App\Models\publicacion;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicacionesActionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('publicaciones')) {
            Schema::create('publicaciones', function ($table) {
                $table->id();
                $table->integer('idWEB_Prod')->nullable();
                $table->integer('cantidad')->default(0);
                $table->decimal('precio_venta', 12, 2)->default(0);
                $table->string('observ', 255)->nullable();
                $table->string('estado', 1)->default('A');
                $table->string('created_by', 50)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inventarios')) {
            Schema::create('inventarios', function ($table) {
                $table->id('Inv_idWEB');
                $table->integer('Inv_IdProd')->nullable();
                $table->integer('Inv_Sucursal')->default(0);
                $table->decimal('Inv_Stock', 12, 2)->default(0);
            });
        }

        if (! Schema::hasTable('marcas')) {
            Schema::create('marcas', function ($table) {
                $table->id();
                $table->string('nombre', 100)->nullable();
            });
        }
    }

    public function test_crear_publicacion_creates_record(): void
    {
        $producto = producto::factory()->create();

        $response = app(CrearPublicacionAction::class)([
            'idwebProd' => $producto->Prod_idWEB,
            'cantidad' => 5,
            'precio' => 1500.00,
            'observ' => 'Test publicacion',
        ]);

        $this->assertDatabaseHas('publicaciones', [
            'idWEB_Prod' => $producto->Prod_idWEB,
            'cantidad' => 5,
            'precio_venta' => 1500.00,
            'estado' => 'A',
        ]);
    }

    public function test_crear_publicacion_returns_error_on_missing_product(): void
    {
        $result = app(CrearPublicacionAction::class)([
            'idwebProd' => 99999,
            'cantidad' => 1,
            'precio' => 100,
            'observ' => '',
        ]);

        $this->assertStringContainsString('Error', $result);
    }

    public function test_registrar_venta_online_pausa_publicacion(): void
    {
        $producto = producto::factory()->create();
        $publicacion = publicacion::create([
            'idWEB_Prod' => $producto->Prod_idWEB,
            'cantidad' => 1,
            'precio_venta' => 1000,
            'estado' => 'A',
        ]);

        $response = app(RegistrarVentaOnlineAction::class)([
            'idweb' => $publicacion->id,
            'accion' => 'P',
            'observ' => 'Pausado por test',
        ]);

        $this->assertDatabaseHas('publicaciones', [
            'id' => $publicacion->id,
            'estado' => 'P',
        ]);
    }

    public function test_registrar_venta_online_returns_error_on_missing_publicacion(): void
    {
        $result = app(RegistrarVentaOnlineAction::class)([
            'idweb' => 99999,
            'accion' => 'P',
        ]);

        $this->assertStringContainsString('Error', $result);
    }

    public function test_registrar_venta_online_invalid_action(): void
    {
        $producto = producto::factory()->create();
        $publicacion = publicacion::create([
            'idWEB_Prod' => $producto->Prod_idWEB,
            'cantidad' => 1,
            'precio_venta' => 1000,
            'estado' => 'A',
        ]);

        $response = app(RegistrarVentaOnlineAction::class)([
            'idweb' => $publicacion->id,
            'accion' => 'X',
        ]);

        $this->assertEquals('Acción no válida', $response->getData(true)['msg']);
    }
}
