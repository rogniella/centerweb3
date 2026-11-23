<?php

namespace Tests\Feature;

use App\Models\famcategoria;
use App\Models\famitemcat;
use App\Models\producto;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductosCategoriasTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registrarSubstringIndex();
        $this->crearTablas();
    }

    public function test_categorias_devuelve_vacio_para_una_familia_sin_configuracion(): void
    {
        $respuesta = $this->actingAs(User::factory()->create())
            ->getJson(route('productos.categorias', ['familia' => 'ZZZ']));

        $respuesta->assertOk()->assertExactJson([]);
    }

    public function test_categorias_devuelve_vacio_sin_familia(): void
    {
        $respuesta = $this->actingAs(User::factory()->create())
            ->getJson(route('productos.categorias'));

        $respuesta->assertOk()->assertExactJson([]);
    }

    public function test_categorias_devuelve_los_niveles_y_respeta_el_padre(): void
    {
        $this->crearCategoria(1, 'CEL', 1, 2, 'Marca');
        $this->crearCategoria(2, 'CEL', 3, 6, 'Modelo');

        $this->crearItem(1, 'SA', 'Samsung', '*');
        $this->crearItem(1, 'IP', 'Apple', '*');
        $this->crearItem(2, 'GAL', 'Galaxy', 'SA');
        $this->crearItem(2, 'IPH', 'iPhone', 'IP');

        $respuesta = $this->actingAs(User::factory()->create())
            ->getJson(route('productos.categorias', [
                'familia' => 'CEL',
                'niveles' => [0 => 'SA'],
            ]));

        $respuesta->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.descripcion', 'Marca')
            ->assertJsonPath('1.descripcion', 'Modelo');

        $codigos = array_column($respuesta->json('1.opciones'), 'codigo');

        $this->assertContains('GAL', $codigos);
        $this->assertNotContains('IPH', $codigos);
    }

    public function test_buscar_aplica_los_niveles_seleccionados(): void
    {
        $this->crearCategoria(1, 'CEL', 1, 2, 'Marca');
        $this->crearCategoria(2, 'CEL', 3, 6, 'Modelo');

        $this->crearProducto('SAGALXYZ', 'CEL', '0001');
        $this->crearProducto('IPIPHXYZ', 'CEL', '0002');
        $this->crearProducto('SAGALXYZ', 'LEN', '0003');

        $this->registrarSubstringIndex();

        $resultados = producto::listar('CEL', '', 10000, '', '', 'T', '', [
            0 => 'SA',
            1 => 'GALXYZ',
        ]);

        $this->assertCount(1, $resultados);
        $this->assertSame('0001', $resultados[0]->Prod_Id);
    }

    public function test_buscar_ignora_el_valor_todos(): void
    {
        $this->crearCategoria(1, 'CEL', 1, 2, 'Marca');
        $this->crearCategoria(2, 'CEL', 3, 6, 'Modelo');

        $this->crearProducto('SAGALXYZ', 'CEL', '0001');
        $this->crearProducto('IPIPHXYZ', 'CEL', '0002');

        $this->registrarSubstringIndex();

        $resultados = producto::listar('CEL', '', 10000, '', '', 'T', '', [
            0 => '00',
            1 => '00',
        ]);

        $this->assertCount(2, $resultados);
    }

    public function test_la_vista_envia_los_niveles_en_las_tres_acciones(): void
    {
        $respuesta = $this->actingAs(User::factory()->create())
            ->get(route('productos.index'));

        $respuesta->assertOk()
            ->assertSee('categoriasFamilia')
            ->assertSee('MÁS OPCIONES')
            ->assertSee('accordion-title-text', false)
            ->assertSee("'MENOS OPCIONES'", false)
            ->assertDontSee('form-inline')
            ->assertSee('col-md-3 col-sm-6', false)
            ->assertSee('<label for="filtro_flia">Familia</label>', false)
            ->assertSee('<label for="filtro1">Descripción</label>', false)
            ->assertSee('<label for="filtroMarca">Marca</label>', false)
            ->assertSee('<label for="filtroEstado">Estado</label>', false)
            ->assertSee('<label for="filtroStock">Stock</label>', false)
            ->assertSee('<label for="mes_ventas">Meses de Ventas</label>', false)
            ->assertSee('<label for="filtro2">Precio</label>', false)
            ->assertSee('<label for="filtro3">Fecha Última Actualización</label>', false)
            ->assertSee('col-sm-12 text-right', false)
            ->assertSee('data: datosFiltrosProductos()', false)
            ->assertSee("url:   'buscar'", false)
            ->assertSee('$.extend({}, datosFiltrosProductos()', false)
            ->assertSee("url:   'registrar_precio_masivo'", false)
            ->assertSee("url:   'genera_pedido'", false);

        $this->assertSame(2, substr_count($respuesta->getContent(), 'data: datosFiltrosProductos()'));
    }

    private function registrarSubstringIndex(): void
    {
        DB::connection()->getPdo()->sqliteCreateFunction(
            'SUBSTRING_INDEX',
            function ($texto, $delimitador, $cantidad): string {
                $partes = explode((string) $delimitador, (string) $texto);

                if ($cantidad >= 0) {
                    return implode((string) $delimitador, array_slice($partes, 0, $cantidad));
                }

                return implode((string) $delimitador, array_slice($partes, $cantidad));
            }
        );
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

        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table): void {
                $table->increments('Prod_idWEB');
                $table->string('Prod_Familia', 8)->nullable();
                $table->string('Prod_Id', 20)->nullable();
                $table->string('Prod_Categoria', 60)->nullable();
                $table->string('Prod_Descripcion', 120)->nullable();
                $table->decimal('Prod_Costo', 12, 2)->default(0);
                $table->decimal('Prod_Precio', 12, 2)->default(0);
                $table->decimal('Prod_Precio2', 12, 2)->default(0);
                $table->string('Prod_Estado', 2)->nullable();
                $table->string('Prod_Marca', 20)->nullable();
                $table->timestamp('Prod_FecAlta')->nullable();
                $table->timestamp('Prod_FecUltMan')->nullable();
                $table->string('Prod_UsuUltMan', 20)->nullable();
                $table->string('Prod_UsuAlta', 20)->nullable();
            });
        }

        if (! Schema::hasTable('inventarios')) {
            Schema::create('inventarios', function (Blueprint $table): void {
                $table->integer('Inv_IdProd');
                $table->integer('Inv_Sucursal');
                $table->integer('Inv_Stock')->default(0);
            });
        }

        if (! Schema::hasTable('moviproductos')) {
            Schema::create('moviproductos', function (Blueprint $table): void {
                $table->string('Mov_Familia', 8)->nullable();
                $table->string('Mov_IdProd', 20)->nullable();
                $table->string('Mov_FecMov', 20)->nullable();
                $table->string('Mov_Operacion', 2)->nullable();
                $table->integer('Mov_Sucursal')->default(0);
                $table->decimal('Mov_Cantidad', 12, 2)->default(0);
            });
        }

        if (! Schema::hasTable('familias')) {
            Schema::create('familias', function (Blueprint $table): void {
                $table->string('Flia_Id', 8)->primary();
                $table->string('Flia_Descripcion', 60)->nullable();
                $table->string('Flia_estado', 2)->nullable();
            });
        }

        if (! Schema::hasTable('monedas')) {
            Schema::create('monedas', function (Blueprint $table): void {
                $table->string('mon_moneda', 8)->primary();
                $table->string('mon_descripcion', 60)->nullable();
                $table->string('mon_estado', 2)->nullable();
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

    private function crearProducto(string $categoria, string $familia, string $id): producto
    {
        return producto::factory()->create([
            'Prod_Familia' => $familia,
            'Prod_Id' => $id,
            'Prod_Categoria' => $categoria,
            'Prod_Descripcion' => 'Lente ' . $id,
            'Prod_Estado' => '',
            'Prod_Marca' => 0,
        ]);
    }
}
