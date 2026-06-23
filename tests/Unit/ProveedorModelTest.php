<?php

namespace Tests\Unit;

use App\Models\proveedor;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProveedorModelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_can_create_proveedor(): void
    {
        $proveedor = proveedor::factory()->create();

        $this->assertNotNull($proveedor->Prov_id);
        $this->assertNotEmpty($proveedor->Prov_RazSocial);
    }

    public function test_proveedor_can_be_deleted(): void
    {
        $proveedor = proveedor::factory()->create();

        $proveedor->delete();

        $this->assertDatabaseMissing('proveedores', [
            'Prov_id' => $proveedor->Prov_id,
        ]);
    }
}
