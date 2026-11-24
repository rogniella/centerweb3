<?php

namespace Tests\Feature;

use App\Actions\Productos\BuscarProductosPorCategoriasAction;
use App\Models\famcategoria;
use App\Models\producto;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuscarProductosPorCategoriasActionTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearTablas();
    }

    public function test_busca_por_familia_y_niveles_sin_campos_de_cristales(): void
    {
        $this->crearCategoria(1, 'CEL', 1, 2, 'Marca');
        $this->crearCategoria(2, 'CEL', 3, 6, 'Modelo');

        $this->crearProducto('SAGALXYZ', 'CEL', '0001');
        $this->crearProducto('SAGALXYZ', 'LEN', '0002');

        $resultados = app(BuscarProductosPorCategoriasAction::class)('CEL', [
            0 => 'SA',
            1 => 'GALXYZ',
        ]);

        $this->assertCount(1, $resultados);
        $this->assertSame(['0001'], array_column($resultados, 'codigo'));
        $this->assertSame(
            ['idweb', 'descripcion', 'codigo', 'categoria', 'precio', 'precio2'],
            array_keys($resultados[0])
        );
        $this->assertArrayNotHasKey('rangos', $resultados[0]);
    }

    public function test_ordena_los_resultados_por_precio_de_menor_a_mayor(): void
    {
        $this->crearCategoria(1, 'CEL', 1, 2, 'Marca');
        $this->crearCategoria(2, 'CEL', 3, 6, 'Modelo');

        $this->crearProducto('SAGALXYZ', 'CEL', '0001', 'Lente', 200);
        $this->crearProducto('SAGALXYZ', 'CEL', '0002', 'Lente', 100);
        $this->crearProducto('SAGALXYZ', 'CEL', '0003', 'Lente', 150);

        $resultados = app(BuscarProductosPorCategoriasAction::class)('CEL', [
            0 => 'SA',
            1 => 'GALXYZ',
        ]);

        $this->assertSame(['0002', '0003', '0001'], array_column($resultados, 'codigo'));
        $this->assertSame([100.0, 150.0, 200.0], array_column($resultados, 'precio'));
    }

    public function test_escapa_comodines_en_la_descripcion(): void
    {
        $this->crearCategoria(1, 'CEL', 1, 2, 'Marca');
        $this->crearCategoria(2, 'CEL', 3, 6, 'Modelo');

        $this->crearProducto('SAGALXYZ', 'CEL', '0001', 'Lente 100% Antirreflex');
        $this->crearProducto('SAGALXYZ', 'CEL', '0002', 'Lente 100X Antirreflex');

        $resultados = app(BuscarProductosPorCategoriasAction::class)(
            'CEL',
            [0 => 'SA', 1 => 'GALXYZ'],
            '100%'
        );

        $this->assertSame(['0001'], array_column($resultados, 'codigo'));
    }

    private function crearTablas(): void
    {
        if (! Schema::hasTable('famcategorias')) {
            Schema::create('famcategorias', function (Blueprint $table): void {
                $table->id('FCat_IdCategoria');
                $table->string('FCat_Familia', 8)->nullable();
                $table->integer('FCat_PosIni')->default(0);
                $table->integer('FCat_CantPos')->default(0);
                $table->string('FCat_Descripcion', 50)->nullable();
                $table->string('FCat_Glosa', 50)->nullable();
            });
        }
    }

    private function crearCategoria(int $id, string $familia, int $posicion, int $longitud, string $descripcion): void
    {
        famcategoria::create([
            'FCat_IdCategoria' => $id,
            'FCat_Familia' => $familia,
            'FCat_PosIni' => $posicion,
            'FCat_CantPos' => $longitud,
            'FCat_Descripcion' => $descripcion,
            'FCat_Glosa' => null,
        ]);
    }

    private function crearProducto(string $categoria, string $familia, string $id, string $descripcion = 'Lente', float $precio = 100): producto
    {
        return producto::factory()->create([
            'Prod_Familia' => $familia,
            'Prod_Id' => $id,
            'Prod_Categoria' => $categoria,
            'Prod_Descripcion' => $descripcion,
            'Prod_Precio' => $precio,
            'Prod_Estado' => '',
        ]);
    }
}
