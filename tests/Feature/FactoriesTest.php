<?php

namespace Tests\Feature;

use App\Models\cliente;
use App\Models\producto;
use App\Models\proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FactoriesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_factory_creates_admin_user(): void
    {
        $user = User::factory()->create(['perfil_id' => 'ADM']);

        $this->assertEquals('ADM', $user->perfil_id);
        $this->assertDatabaseHas('users', [
            'name' => $user->name,
            'perfil_id' => 'ADM',
        ]);
    }

    public function test_producto_factory_creates_valid_product(): void
    {
        $producto = producto::factory()->create();

        $this->assertDatabaseHas('productos', [
            'Prod_idWEB' => $producto->Prod_idWEB,
        ]);
    }

    public function test_cliente_factory_creates_valid_client(): void
    {
        $cliente = cliente::factory()->create();

        $this->assertDatabaseHas('clientes', [
            'Cli_idWEB' => $cliente->Cli_idWEB,
        ]);
    }

    public function test_proveedor_factory_creates_valid_supplier(): void
    {
        $proveedor = proveedor::factory()->create();

        $this->assertDatabaseHas('proveedores', [
            'Prov_id' => $proveedor->Prov_id,
        ]);
    }
}
