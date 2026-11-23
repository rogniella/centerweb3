<?php

namespace Tests\Feature;

use App\Models\cristal;
use App\Models\famcategoria;
use App\Models\famitemcat;
use App\Models\producto;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsultaTipoCristalesTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearTablas();
        $this->crearCategorias();
    }

    public function test_index_muestra_las_cuatro_categorias_y_sus_opciones(): void
    {
        famitemcat::create([
            'FCatItem_IdCategoria' => 11,
            'FCatItem_Codigo' => 'M',
            'FCatItem_Descripcion' => 'MULTIFOCAL',
            'FCatItem_CodPadre' => '*',
        ]);
        famitemcat::create([
            'FCatItem_IdCategoria' => 12,
            'FCatItem_Codigo' => 'M',
            'FCatItem_Descripcion' => 'Mineral',
            'FCatItem_CodPadre' => '*',
        ]);
        famitemcat::create([
            'FCatItem_IdCategoria' => 13,
            'FCatItem_Codigo' => 'P',
            'FCatItem_Descripcion' => 'Polarizado',
            'FCatItem_CodPadre' => '*',
        ]);
        famitemcat::create([
            'FCatItem_IdCategoria' => 14,
            'FCatItem_Codigo' => 'ADV',
            'FCatItem_Descripcion' => 'ADVANCE',
            'FCatItem_CodPadre' => '*',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('cristales.consultatipo'))
            ->assertOk()
            ->assertSee('Consulta Tipo Cristales')
            ->assertSee('Tipo Lente')
            ->assertSee('MULTIFOCAL')
            ->assertSee('Mineral')
            ->assertSee('Polarizado')
            ->assertSee('ADVANCE')
            ->assertSee('Rangos')
            ->assertDontSee('Categoría')
            ->assertDontSee('Precio 2')
            ->assertSee('step="0.25" class="form-control" id="graduacion_esf"', false)
            ->assertSee('step="0.25" class="form-control" id="graduacion_cil"', false)
            ->assertSee('mensajeErrorValidacionTipoCristal')
            ->assertDontSee('Existentes en Stock')
            ->assertDontSee('Stock Suc. 1')
            ->assertDontSee('Stock Suc. 2')
            ->assertViewHas('categorias', fn ($categorias): bool => count($categorias) === 4);
    }

    public function test_home_incluye_consulta_tipo_cristales_en_accesos_directos(): void
    {
        $usuario = User::factory()->create(['home_shortcuts' => null]);

        $this->actingAs($usuario)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Configurar Accesos Directos')
            ->assertSee('Consulta Tipo Cristales')
            ->assertSee('fa fa-eye');

        $this->assertSame('fa-eye', config('home.shortcuts')['cristales.consultatipo']['icon']);
    }

    public function test_endpoint_categorias_aplica_la_cascada_con_valores_de_productos(): void
    {
        $this->crearProducto('MOPADV');
        $this->crearProducto('MSPVAR');
        $this->crearProducto('SXPVAR');

        $url = $this->urlConParametros(route('cristales.consultatipo.categorias'), [
            'nivel' => 1,
            'niveles' => [0 => 'M'],
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);

        $respuesta->assertOk()
            ->assertJsonPath('indice', 1)
            ->assertJsonPath('posicion', 2);

        $codigos = collect($respuesta->json('opciones'))->pluck('codigo')->all();

        $this->assertContains('O', $codigos);
        $this->assertContains('S', $codigos);
        $this->assertNotContains('X', $codigos);
    }

    public function test_cascada_respeta_el_codigo_padre_de_famitemcat(): void
    {
        famitemcat::create([
            'FCatItem_IdCategoria' => 13,
            'FCatItem_Codigo' => 'P',
            'FCatItem_Descripcion' => 'Polarizado',
            'FCatItem_CodPadre' => 'O',
        ]);
        famitemcat::create([
            'FCatItem_IdCategoria' => 13,
            'FCatItem_Codigo' => 'Z',
            'FCatItem_Descripcion' => 'Otro padre',
            'FCatItem_CodPadre' => 'X',
        ]);

        $this->crearProducto('MOPVAR');

        $url = $this->urlConParametros(route('cristales.consultatipo.categorias'), [
            'nivel' => 2,
            'niveles' => [0 => 'M', 1 => 'O'],
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $codigos = collect($respuesta->json('opciones'))->pluck('codigo')->all();

        $this->assertContains('P', $codigos);
        $this->assertNotContains('Z', $codigos);
    }

    public function test_buscar_filtra_por_cuatro_posiciones_y_excluye_inactivos(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $inactivo = $this->crearProducto('MOPADV', ['Prod_Id' => '0002']);
        $this->crearProducto('MSPVAR', ['Prod_Id' => '0003']);

        producto::query()
            ->where('Prod_idWEB', $inactivo->Prod_idWEB)
            ->update(['Prod_Estado' => 'I']);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [
                0 => 'M',
                1 => 'O',
                2 => 'P',
                3 => 'ADV',
            ],
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);

        $respuesta->assertOk();

        $this->assertSame(['0001'], collect($respuesta->json())->pluck('codigo')->all());
    }

    public function test_buscar_incluye_productos_con_estado_nulo(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $sinEstado = $this->crearProducto('MOPADV', ['Prod_Id' => '0002']);

        producto::query()
            ->where('Prod_idWEB', $sinEstado->Prod_idWEB)
            ->update(['Prod_Estado' => null]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $this->assertSame(['0001', '0002'], collect($respuesta->json())->pluck('codigo')->all());
    }

    public function test_buscar_escape_comodines_en_la_descripcion(): void
    {
        $this->crearProducto('MOPADV', [
            'Prod_Id' => '0001',
            'Prod_Descripcion' => 'Lente 100% Antirreflex',
        ]);
        $this->crearProducto('MOPADV', [
            'Prod_Id' => '0002',
            'Prod_Descripcion' => 'Lente 100X Antirreflex',
        ]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'descripcion' => '100%',
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $this->assertSame(['0001'], collect($respuesta->json())->pluck('codigo')->all());
    }

    public function test_buscar_devuelve_un_rango_cuando_el_producto_tiene_un_registro(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $this->crearCristal('0001', [
            'Cri_esf' => 6,
            'Cri_esf_neg' => -10,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $this->assertSame(['Esf 6/-10 Cil 6/-6'], $respuesta->json('0.rangos'));
    }

    public function test_buscar_devuelve_un_renglon_por_rango_cuando_hay_varios_registros(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $this->crearCristal('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4.25,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);
        $this->crearCristal('0001', [
            'Cri_esf' => -8.25,
            'Cri_esf_neg' => 0,
            'Cri_cil' => -2.25,
            'Cri_cil_neg' => 0,
        ]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $this->assertSame([
            'Esf -8.25/0 Cil -2.25/0',
            'Esf 4/-4.25 Cil 6/-6',
        ], $respuesta->json('0.rangos'));
    }

    public function test_buscar_ignora_el_parametro_stock_y_devuelve_rangos_vacios(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'stock' => 1,
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $producto = $respuesta->json('0');

        $this->assertSame([], $producto['rangos']);
        $this->assertArrayNotHasKey('stock01', $producto);
        $this->assertArrayNotHasKey('stock02', $producto);
    }

    public function test_buscar_sin_coincidencias_devuelve_lista_vacia(): void
    {
        $this->crearProducto('MOPADV');

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [3 => 'ZZZ'],
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson($url)
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_buscar_filtra_por_graduaciones_dentro_del_rango(): void
    {
        $this->crearProductoConRango('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);
        $this->crearProductoConRango('0002', [
            'Cri_esf' => 2,
            'Cri_esf_neg' => -2,
            'Cri_cil' => 2,
            'Cri_cil_neg' => -2,
        ]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'esf' => 3,
            'cil' => 5,
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $this->assertSame(['0001'], collect($respuesta->json())->pluck('codigo')->all());
    }

    public function test_buscar_oculta_productos_fuera_del_rango(): void
    {
        $this->crearProductoConRango('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'esf' => 4.25,
            'cil' => 5,
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson($url)
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_buscar_acepta_valores_exactos_en_los_limites_del_rango(): void
    {
        $this->crearProductoConRango('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'esf' => -4,
            'cil' => 6,
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $this->assertSame(['0001'], collect($respuesta->json())->pluck('codigo')->all());
    }

    public function test_buscar_filtra_decimales_y_graduaciones_negativas(): void
    {
        $this->crearProductoConRango('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4.25,
            'Cri_cil' => -2.25,
            'Cri_cil_neg' => -4,
        ]);

        $urlDentro = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'esf' => -4.25,
            'cil' => -3,
        ]);
        $urlFuera = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'esf' => -4.25,
            'cil' => -4.25,
        ]);

        $this->actingAs(User::factory()->create());

        $respuestaDentro = $this->getJson($urlDentro);
        $respuestaDentro->assertOk();

        $this->assertSame(
            ['0001'],
            collect($respuestaDentro->json())->pluck('codigo')->all()
        );

        $this->getJson($urlFuera)
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_buscar_aplica_cada_graduacion_de_forma_independiente(): void
    {
        $this->crearProductoConRango('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);
        $this->crearProductoConRango('0002', [
            'Cri_esf' => 2,
            'Cri_esf_neg' => -2,
            'Cri_cil' => 2,
            'Cri_cil_neg' => -2,
        ]);

        $parametros = [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
        ];

        $urlSoloEsfera = $this->urlConParametros(
            route('cristales.consultatipo.buscar'),
            $parametros + ['esf' => 1]
        );
        $urlSoloCilindro = $this->urlConParametros(
            route('cristales.consultatipo.buscar'),
            $parametros + ['cil' => 5]
        );

        $this->actingAs(User::factory()->create());

        $respuestaEsfera = $this->getJson($urlSoloEsfera);
        $respuestaCilindro = $this->getJson($urlSoloCilindro);

        $respuestaEsfera->assertOk();
        $respuestaCilindro->assertOk();

        $this->assertSame(
            ['0001', '0002'],
            collect($respuestaEsfera->json())->pluck('codigo')->all()
        );
        $this->assertSame(
            ['0001'],
            collect($respuestaCilindro->json())->pluck('codigo')->all()
        );
    }

    public function test_buscar_con_un_rango_compatible_conserva_todos_los_rangos(): void
    {
        $this->crearProductoConRango('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 4,
            'Cri_cil_neg' => 2,
        ]);
        $this->crearCristal('0001', [
            'Cri_esf' => -8.25,
            'Cri_esf_neg' => 0,
            'Cri_cil' => -2.25,
            'Cri_cil_neg' => -4,
        ]);
        $this->crearProductoConRango('0002', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 4,
            'Cri_cil_neg' => 2,
        ]);

        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
            'esf' => -1,
            'cil' => -3,
        ]);

        $respuesta = $this->actingAs(User::factory()->create())->getJson($url);
        $respuesta->assertOk();

        $this->assertSame(['0001'], collect($respuesta->json())->pluck('codigo')->all());
        $this->assertSame([
            'Esf -8.25/0 Cil -2.25/-4',
            'Esf 4/-4 Cil 4/2',
        ], $respuesta->json('0.rangos'));
    }

    public function test_buscar_oculta_productos_sin_registros_de_cristales_cuando_hay_filtro(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);

        $parametros = [
            'niveles' => [0 => 'M', 1 => 'O', 2 => 'P', 3 => 'ADV'],
        ];

        $urlConFiltro = $this->urlConParametros(
            route('cristales.consultatipo.buscar'),
            $parametros + ['esf' => 0]
        );
        $urlSinFiltro = $this->urlConParametros(route('cristales.consultatipo.buscar'), $parametros);

        $this->actingAs(User::factory()->create());

        $this->getJson($urlConFiltro)
            ->assertOk()
            ->assertExactJson([]);

        $respuesta = $this->getJson($urlSinFiltro);
        $respuesta->assertOk();

        $this->assertSame([], $respuesta->json('0.rangos'));
    }

    public function test_buscar_valida_que_las_graduaciones_sean_numericas(): void
    {
        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'esf' => 'abc',
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson($url)
            ->assertStatus(422)
            ->assertJsonValidationErrors('esf');
    }

    public function test_buscar_valida_que_las_graduaciones_sean_multiplos_de_025(): void
    {
        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'esf' => '-3.10',
            'cil' => '-2.20',
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson($url)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['esf', 'cil'])
            ->assertJsonPath('errors.esf.0', 'El campo esfera debe ser múltiplo de 0.25')
            ->assertJsonPath('errors.cil.0', 'El campo cilindro debe ser múltiplo de 0.25');
    }

    public function test_buscar_acepta_graduaciones_multiplos_de_025(): void
    {
        $url = $this->urlConParametros(route('cristales.consultatipo.buscar'), [
            'esf' => '-3.25',
            'cil' => '-1.75',
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson($url)
            ->assertOk();
    }

    public function test_admin_ve_columna_y_modal_de_administracion_de_rangos(): void
    {
        $this->actingAs($this->administrador())
            ->get(route('cristales.consultatipo'))
            ->assertOk()
            ->assertSee('Acciones')
            ->assertSee('Administrar rangos')
            ->assertSee('modalRangosCristal')
            ->assertSee('btn-eliminar-rango')
            ->assertSee('Eliminar rango');
    }

    public function test_usuario_no_admin_no_ve_administracion_de_rangos(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('cristales.consultatipo'))
            ->assertOk()
            ->assertDontSee('Acciones')
            ->assertDontSee('Administrar rangos')
            ->assertDontSee('modalRangosCristal')
            ->assertDontSee('btn-eliminar-rango')
            ->assertDontSee('Eliminar rango');
    }

    public function test_admin_puede_listar_los_rangos_del_producto(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $primerRango = $this->crearCristal('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);
        $segundoRango = $this->crearCristal('0001', [
            'Cri_esf' => -8.25,
            'Cri_esf_neg' => 0,
            'Cri_cil' => -2.25,
            'Cri_cil_neg' => 0,
        ]);

        $respuesta = $this->actingAs($this->administrador())
            ->getJson(route('cristales.rangos.index', ['producto' => '0001']));

        $respuesta->assertOk()->assertJsonCount(2);

        $this->assertSame(
            [$primerRango->Cri_rango_id, $segundoRango->Cri_rango_id],
            collect($respuesta->json())->pluck('Cri_rango_id')->all()
        );
    }

    public function test_usuario_no_admin_no_puede_acceder_a_los_endpoints_de_rangos(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('cristales.rangos.index', ['producto' => '0001']))
            ->assertStatus(401);
    }

    public function test_admin_puede_crear_un_rango_y_la_familia_queda_fija(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);

        $respuesta = $this->actingAs($this->administrador())
            ->postJson(route('cristales.rangos.store', ['producto' => '0001']), [
                'Cri_familia' => 'CEL',
                'Cri_esf' => 4,
                'Cri_esf_neg' => -4.25,
                'Cri_cil' => -2.25,
                'Cri_cil_neg' => -4,
                'Cri_add' => 1.5,
                'Cri_rango' => 2,
                'Cri_metodo' => 3,
            ]);

        $respuesta->assertCreated()
            ->assertJsonPath('Cri_familia', 'LEN')
            ->assertJsonPath('Cri_id', '0001');

        $this->assertDatabaseHas('cristales', [
            'Cri_familia' => 'LEN',
            'Cri_id' => '0001',
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4.25,
            'Cri_cil' => -2.25,
            'Cri_cil_neg' => -4,
            'Cri_add' => 1.5,
            'Cri_rango' => 2,
            'Cri_metodo' => 3,
        ]);
    }

    public function test_admin_puede_actualizar_un_rango(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $rango = $this->crearCristal('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);

        $this->actingAs($this->administrador())
            ->putJson(route('cristales.rangos.update', ['producto' => '0001', 'rango' => $rango->Cri_rango_id]), [
                'Cri_esf' => 6,
                'Cri_esf_neg' => -10,
                'Cri_cil' => 4,
                'Cri_cil_neg' => -4,
                'Cri_add' => 2,
                'Cri_rango' => 1,
                'Cri_metodo' => 2,
            ])
            ->assertOk();

        $this->assertDatabaseHas('cristales', [
            'Cri_rango_id' => $rango->Cri_rango_id,
            'Cri_familia' => 'LEN',
            'Cri_id' => '0001',
            'Cri_esf' => 6,
            'Cri_esf_neg' => -10,
            'Cri_cil' => 4,
            'Cri_cil_neg' => -4,
            'Cri_add' => 2,
            'Cri_rango' => 1,
            'Cri_metodo' => 2,
        ]);
    }

    public function test_admin_puede_eliminar_un_rango(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $rango = $this->crearCristal('0001');

        $this->actingAs($this->administrador())
            ->deleteJson(route('cristales.rangos.destroy', ['producto' => '0001', 'rango' => $rango->Cri_rango_id]))
            ->assertOk();

        $this->assertDatabaseMissing('cristales', ['Cri_rango_id' => $rango->Cri_rango_id]);
    }

    public function test_no_se_puede_modificar_un_rango_de_otro_producto(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);
        $this->crearProducto('MOPADV', ['Prod_Id' => '0002']);
        $rango = $this->crearCristal('0001', [
            'Cri_esf' => 4,
            'Cri_esf_neg' => -4,
            'Cri_cil' => 6,
            'Cri_cil_neg' => -6,
        ]);

        $this->actingAs($this->administrador());

        $this->putJson(route('cristales.rangos.update', ['producto' => '0002', 'rango' => $rango->Cri_rango_id]), [
            'Cri_esf' => 6,
            'Cri_esf_neg' => -10,
            'Cri_cil' => 4,
            'Cri_cil_neg' => -4,
        ])->assertNotFound();

        $this->deleteJson(route('cristales.rangos.destroy', ['producto' => '0002', 'rango' => $rango->Cri_rango_id]))
            ->assertNotFound();
    }

    public function test_admin_valida_los_campos_del_rango(): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => '0001']);

        $this->actingAs($this->administrador())
            ->postJson(route('cristales.rangos.store', ['producto' => '0001']), [
                'Cri_esf' => 'abc',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['Cri_esf', 'Cri_esf_neg', 'Cri_cil', 'Cri_cil_neg']);
    }

    /**
     * @param  array<string, mixed>  $parametros
     */
    private function urlConParametros(string $ruta, array $parametros): string
    {
        return $ruta.'?'.http_build_query($parametros);
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

        if (! Schema::hasTable('cristales')) {
            Schema::create('cristales', function (Blueprint $table): void {
                $table->bigIncrements('Cri_rango_id')->first();
                $table->string('Cri_familia', 10);
                $table->string('Cri_id', 20);
                $table->float('Cri_esf')->nullable();
                $table->float('Cri_esf_neg')->nullable();
                $table->float('Cri_cil')->nullable();
                $table->float('Cri_cil_neg')->nullable();
                $table->float('Cri_add')->nullable();
                $table->integer('Cri_rango')->nullable();
                $table->integer('Cri_metodo')->nullable();
                $table->index(['Cri_familia', 'Cri_id']);
            });
        }
    }

    private function crearCategorias(): void
    {
        $categorias = [
            ['id' => 11, 'pos' => 1, 'len' => 1, 'descripcion' => 'Tipo Lente'],
            ['id' => 12, 'pos' => 2, 'len' => 1, 'descripcion' => 'Material'],
            ['id' => 13, 'pos' => 3, 'len' => 1, 'descripcion' => 'Clase'],
            ['id' => 14, 'pos' => 4, 'len' => 3, 'descripcion' => 'Producto Lente'],
        ];

        foreach ($categorias as $categoria) {
            famcategoria::create([
                'FCat_IdCategoria' => $categoria['id'],
                'FCat_Familia' => 'LEN',
                'FCat_PosIni' => $categoria['pos'],
                'FCat_CantPos' => $categoria['len'],
                'FCat_Descripcion' => $categoria['descripcion'],
                'FCat_Glosa' => null,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function crearProducto(string $categoria, array $atributos = []): producto
    {
        return producto::factory()->create(array_merge([
            'Prod_Familia' => 'LEN',
            'Prod_Id' => fake()->unique()->numerify('#####'),
            'Prod_Categoria' => $categoria,
            'Prod_Descripcion' => 'Lente '.$categoria,
            'Prod_Precio' => 100,
            'Prod_Precio2' => 80,
            'Prod_Estado' => '',
        ], $atributos));
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function crearCristal(string $id, array $atributos = []): cristal
    {
        return cristal::factory()->create(array_merge([
            'Cri_familia' => 'LEN',
            'Cri_id' => $id,
            'Cri_esf' => 0,
            'Cri_esf_neg' => 0,
            'Cri_cil' => 0,
            'Cri_cil_neg' => 0,
            'Cri_add' => 0,
            'Cri_rango' => null,
            'Cri_metodo' => null,
        ], $atributos));
    }

    /**
     * @param  array<string, mixed>  $rango
     */
    private function crearProductoConRango(string $id, array $rango): void
    {
        $this->crearProducto('MOPADV', ['Prod_Id' => $id]);
        $this->crearCristal($id, $rango);
    }

    private function administrador(): User
    {
        return User::factory()->create(['perfil_id' => 'ADM']);
    }
}
