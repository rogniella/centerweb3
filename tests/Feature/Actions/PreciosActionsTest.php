<?php

namespace Tests\Feature\Actions;

use App\Actions\Precios\GuardarPrecioAction;
use App\Actions\Precios\LeerPrecioAction;
use App\Actions\Precios\ModificarPrecioAction;
use App\Models\producto;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PreciosActionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('precios')) {
            Schema::create('precios', function ($table) {
                $table->id();
                $table->integer('idWEB_Prod')->nullable();
                $table->decimal('precio', 12, 2)->default(0);
                $table->decimal('precio2', 12, 2)->default(0);
                $table->decimal('costo', 12, 2)->default(0);
                $table->string('idlista', 10)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cotizacion')) {
            Schema::create('cotizacion', function ($table) {
                $table->id();
                $table->string('Cot_Moneda', 10)->nullable();
                $table->date('cot_fecmov')->nullable();
                $table->decimal('cot_cotizacion', 12, 4)->default(1);
            });
        }
    }

    public function test_leer_precio_returns_empty_when_not_found(): void
    {
        $response = app(LeerPrecioAction::class)(99999);
        $data = $response->getData(true);

        $this->assertEquals('', $data['idLista']);
        $this->assertEquals(0, $data['precio']);
    }

    public function test_leer_precio_returns_data_when_found(): void
    {
        $producto = producto::factory()->create();
        \Illuminate\Support\Facades\DB::table('precios')->insert([
            'idWEB_Prod' => $producto->Prod_idWEB,
            'precio' => 2500,
            'precio2' => 2250,
            'costo' => 1000,
            'idlista' => 'R',
        ]);

        $response = app(LeerPrecioAction::class)($producto->Prod_idWEB);
        $data = $response->getData(true);

        $this->assertEquals(2500, $data['precio']);
        $this->assertEquals(2250, $data['precio2']);
        $this->assertEquals(1000, $data['costo']);
    }

    public function test_guardar_precio_creates_new_record(): void
    {
        $producto = producto::factory()->create();

        $response = app(GuardarPrecioAction::class)([
            'idprod' => $producto->Prod_idWEB,
            'precio' => 3000,
            'precio2' => 2700,
            'costo' => 1200,
            'idlista' => 'R',
        ]);

        $this->assertDatabaseHas('precios', [
            'idWEB_Prod' => $producto->Prod_idWEB,
            'precio' => 3000,
        ]);
    }

    public function test_guardar_precio_updates_existing_record(): void
    {
        $producto = producto::factory()->create();
        \Illuminate\Support\Facades\DB::table('precios')->insert([
            'idWEB_Prod' => $producto->Prod_idWEB,
            'precio' => 1000,
            'precio2' => 900,
            'costo' => 500,
            'idlista' => 'R',
        ]);

        app(GuardarPrecioAction::class)([
            'idprod' => $producto->Prod_idWEB,
            'precio' => 3500,
            'precio2' => 3150,
            'costo' => 1500,
            'idlista' => 'R',
        ]);

        $this->assertDatabaseHas('precios', [
            'idWEB_Prod' => $producto->Prod_idWEB,
            'precio' => 3500,
        ]);
    }

    public function test_guardar_precio_returns_string_on_missing_product(): void
    {
        $result = app(GuardarPrecioAction::class)([
            'idprod' => 99999,
            'precio' => 100,
            'precio2' => 90,
            'costo' => 50,
            'idlista' => 'R',
        ]);

        $this->assertStringContainsString('Error', $result);
    }

    public function test_modificar_precio_lee_precio(): void
    {
        $producto = producto::factory()->create([
            'Prod_Precio' => 5000,
            'Prod_Precio2' => 4500,
        ]);

        $response = app(ModificarPrecioAction::class)([
            'familia' => $producto->Prod_Familia,
            'codigo' => $producto->Prod_Id,
            'action' => 'lee_precio',
        ]);

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(5000, $data['precio']);
        $this->assertEquals(4500, $data['precio2']);
    }

    public function test_modificar_precio_lee_precio_not_found(): void
    {
        $response = app(ModificarPrecioAction::class)([
            'familia' => 'REC',
            'codigo' => '99999',
            'action' => 'lee_precio',
        ]);

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
    }

    public function test_modificar_precio_invalid_action(): void
    {
        $producto = producto::factory()->create();

        $response = app(ModificarPrecioAction::class)([
            'familia' => $producto->Prod_Familia,
            'codigo' => $producto->Prod_Id,
            'action' => 'invalid',
        ]);

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
    }

    public function test_modificar_precio_modifica_precio(): void
    {
        $producto = producto::factory()->create([
            'Prod_Precio' => 5000,
            'Prod_Precio2' => 4500,
        ]);

        $response = app(ModificarPrecioAction::class)([
            'familia' => $producto->Prod_Familia,
            'codigo' => $producto->Prod_Id,
            'action' => 'modifica_precio',
            'monto' => 6000,
            'monto2' => 5400,
        ]);

        $data = $response->getData(true);
        $this->assertTrue($data['success']);

        $this->assertDatabaseHas('productos', [
            'Prod_idWEB' => $producto->Prod_idWEB,
            'Prod_Precio' => 6000,
            'Prod_Precio2' => 5400,
        ]);
    }
}
