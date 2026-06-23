<?php

namespace Tests\Unit;

use App\Models\cliente;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClienteModelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_can_create_cliente(): void
    {
        $cliente = cliente::factory()->create();

        $this->assertNotNull($cliente->Cli_idWEB);
        $this->assertNotEmpty($cliente->Cli_ApeNom);
    }

    public function test_find_id_returns_client(): void
    {
        $cliente = cliente::factory()->create();

        $found = cliente::find_id($cliente->Cli_Id);

        $this->assertNotNull($found);
        $this->assertEquals($cliente->Cli_idWEB, $found->Cli_idWEB);
    }

    public function test_find_id_returns_null_for_nonexistent(): void
    {
        $found = cliente::find_id(999999);

        $this->assertNull($found);
    }

    public function test_cliente_can_be_deleted(): void
    {
        $cliente = cliente::factory()->create();

        $id = $cliente->Cli_idWEB;
        $cliente->delete();

        $this->assertNull(cliente::find($id));
    }
}
