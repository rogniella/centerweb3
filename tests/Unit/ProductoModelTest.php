<?php

namespace Tests\Unit;

use App\Models\producto;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductoModelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_can_create_producto(): void
    {
        $producto = producto::factory()->create();

        $this->assertNotNull($producto->Prod_idWEB);
        $this->assertNotEmpty($producto->Prod_Descripcion);
    }

    public function test_find_codigo_returns_product(): void
    {
        $producto = producto::factory()->create();

        $found = producto::findCodigo($producto->Prod_Familia, $producto->Prod_Id);

        $this->assertNotNull($found);
        $this->assertEquals($producto->Prod_idWEB, $found->Prod_idWEB);
    }

    public function test_find_codigo_returns_null_for_nonexistent(): void
    {
        $found = producto::findCodigo('REC', '99999');

        $this->assertNull($found);
    }
}
