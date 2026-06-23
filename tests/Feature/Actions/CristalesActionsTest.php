<?php

namespace Tests\Feature\Actions;

use App\Actions\Cristales\ConsultarStockCristalAction;
use App\Models\inventario;
use App\Models\producto;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CristalesActionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('inventarios')) {
            Schema::create('inventarios', function ($table) {
                $table->id('Inv_idWEB');
                $table->integer('Inv_IdProd')->nullable();
                $table->integer('Inv_Sucursal')->default(0);
                $table->decimal('Inv_Stock', 12, 2)->default(0);
            });
        }
    }

    public function test_consultar_stock_cristal_returns_stock(): void
    {
        $producto = producto::factory()->create(['Prod_Familia' => 'CRI', 'Prod_Id' => 'OB100']);

        inventario::create([
            'Inv_idProd' => $producto->Prod_idWEB,
            'Inv_Sucursal' => 1,
            'Inv_Stock' => 15,
        ]);

        inventario::create([
            'Inv_idProd' => $producto->Prod_idWEB,
            'Inv_Sucursal' => 2,
            'Inv_Stock' => 8,
        ]);

        $response = app(ConsultarStockCristalAction::class)('OB100');

        $data = $response->getData(true);
        $this->assertEquals(15, (int) $data['stock01']);
        $this->assertEquals(8, (int) $data['stock02']);
    }

    public function test_consultar_stock_cristal_returns_error_for_missing(): void
    {
        $response = app(ConsultarStockCristalAction::class)('NOEXISTE');

        $data = $response->getData(true);
        $this->assertEquals('ERR1', $data['stock01']);
        $this->assertEquals('ERR1', $data['stock02']);
    }

    public function test_consultar_stock_cristal_returns_zero_when_no_inventory(): void
    {
        $producto = producto::factory()->create(['Prod_Familia' => 'CRI', 'Prod_Id' => 'OB200']);

        $response = app(ConsultarStockCristalAction::class)('OB200');

        $data = $response->getData(true);
        $this->assertEquals(0, (int) $data['stock01']);
        $this->assertEquals(0, (int) $data['stock02']);
    }
}
