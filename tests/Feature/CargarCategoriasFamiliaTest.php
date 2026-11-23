<?php

namespace Tests\Feature;

use App\Actions\Categorias\CargarCategoriasFamiliaAction;
use App\Models\famcategoria;
use App\Models\famitemcat;
use App\Models\producto;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CargarCategoriasFamiliaTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearTablas();
    }

    public function test_devuelve_solo_los_niveles_y_opciones_de_la_familia_recibida(): void
    {
        $this->crearCategoria(1, 'CEL', 1, 2, 'Marca');
        $this->crearCategoria(2, 'CEL', 3, 6, 'Modelo');
        $this->crearCategoria(11, 'LEN', 1, 1, 'Tipo Lente');
        $this->crearCategoria(12, 'LEN', 2, 1, 'Material');

        $this->crearItem(1, 'SA', 'Samsung', '*');
        $this->crearItem(2, 'GAL', 'Galaxy', 'SA');

        $niveles = app(CargarCategoriasFamiliaAction::class)('CEL');

        $this->assertCount(2, $niveles);
        $this->assertSame([1, 3], array_column($niveles, 'posicion'));
        $this->assertSame([2, 6], array_column($niveles, 'longitud'));
        $this->assertSame('Marca', $niveles[0]['descripcion']);
        $this->assertSame([
            ['codigo' => '00', 'descripcion' => '[Todos]'],
            ['codigo' => 'SA', 'descripcion' => 'Samsung'],
        ], $niveles[0]['opciones']);
        $this->assertNotContains('Tipo Lente', array_column($niveles, 'descripcion'));
    }

    public function test_respeta_posiciones_con_salto_y_codigos_de_productos(): void
    {
        $this->crearCategoria(5, 'CRI', 1, 1, 'Material');
        $this->crearCategoria(6, 'CRI', 2, 1, 'Clase');
        $this->crearCategoria(7, 'CRI', 3, 4, 'Grado Esf');
        $this->crearCategoria(8, 'CRI', 7, 3, 'Grado Cil');

        $this->crearProducto('MOAB12CYL', 'CRI');

        $niveles = app(CargarCategoriasFamiliaAction::class)('CRI', [
            0 => 'M',
            1 => 'O',
            2 => 'AB12',
        ]);

        $this->assertSame([1, 2, 3, 7], array_column($niveles, 'posicion'));
        $this->assertSame([1, 1, 4, 3], array_column($niveles, 'longitud'));
        $this->assertContains(['codigo' => 'CYL', 'descripcion' => 'CYL'], $niveles[3]['opciones']);
    }

    public function test_familia_sin_productos_devuelve_opciones_catalogadas(): void
    {
        $this->crearCategoria(1, 'IND', 1, 2, 'Marca');
        $this->crearItem(1, 'ZZ', 'Zeta', '*');

        $niveles = app(CargarCategoriasFamiliaAction::class)('IND');

        $this->assertCount(1, $niveles);
        $this->assertContains(['codigo' => 'ZZ', 'descripcion' => 'Zeta'], $niveles[0]['opciones']);
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

        if (! Schema::hasTable('famitemcat')) {
            Schema::create('famitemcat', function (Blueprint $table): void {
                $table->integer('FCatItem_IdCategoria');
                $table->string('FCatItem_Codigo', 15);
                $table->string('FCatItem_Descripcion', 30)->nullable();
                $table->string('FCatItem_CodPadre', 15)->nullable();
                $table->unique([
                    'FCatItem_IdCategoria',
                    'FCatItem_CodPadre',
                    'FCatItem_Codigo',
                ]);
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

    private function crearItem(int $categoria, string $codigo, string $descripcion, string $padre): void
    {
        famitemcat::create([
            'FCatItem_IdCategoria' => $categoria,
            'FCatItem_Codigo' => $codigo,
            'FCatItem_Descripcion' => $descripcion,
            'FCatItem_CodPadre' => $padre,
        ]);
    }

    private function crearProducto(string $categoria, string $familia): producto
    {
        return producto::factory()->create([
            'Prod_Familia' => $familia,
            'Prod_Categoria' => $categoria,
            'Prod_Estado' => '',
        ]);
    }
}
