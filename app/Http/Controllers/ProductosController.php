<?php

namespace App\Http\Controllers;

/*
    Para manejo de zip Con la vs nueva usamos una extension de PHP
       A partir de PHP 8.2.0, la DLL php_zip.dll debe estar habilitada en php.ini . Anteriormente, esta extensión estaba integrada.
    ya no //use Zipper;
*/

use App\Models\clases\comprobante; // Extension PHP
use App\Models\cotizacion;
use App\Models\familia;
use App\Models\inventario;  // Para usar SQL directamente (Raw SQL)
use App\Models\marca;
use App\Models\moneda;
use App\Models\precio;
use App\Models\producto;
use App\Models\publicacion;
use App\Models\sucursal;
use App\Models\tienda_producto;
use App\Actions\Cristales\ConsultarStockCristalAction;
use App\Actions\Cristales\GenerarPlanillaStockAction;
use App\Actions\Movimientos\BuscarMovimientosAction;
use App\Actions\Movimientos\ListarAuditoriaAction;
use App\Actions\Movimientos\ListarMovimientosAction;
use App\Actions\Precios\ActualizarPrecioExcelAction;
use App\Actions\Precios\ConsultarPrecioDetalleAction;
use App\Actions\Precios\ConsultarPrecioVentaAction;
use App\Actions\Precios\GenerarPedidoPreciosAction;
use App\Actions\Precios\GuardarPrecioAction;
use App\Actions\Precios\LeerPrecioAction;
use App\Actions\Precios\ModificarPrecioAction;
use App\Actions\Precios\RegistrarPrecioMasivoAction;
use App\Actions\Productos\BuscarProductoAction;
use App\Actions\Productos\CambiarCodigoAction;
use App\Actions\Productos\ConsolidarCodigoAction;
use App\Actions\Productos\GenerarNuevoCodigoAction;
use App\Actions\Productos\GenerarPedidoAction;
use App\Actions\Publicaciones\CrearPublicacionAction;
use App\Actions\Publicaciones\ListarPublicacionesAction;
use App\Actions\Publicaciones\RegistrarVentaOnlineAction;
use Codexshaper\WooCommerce\Facades\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
// Ver, por falla en  $sheet->getCell($letraVal.$i)->getCalculatedValue()  tuve que copiar la carpeta que me bajo en vendor, por la que funcionaba Ok , esta en el d:/tools/web/php y excel/ zip .
use PhpOffice\PhpSpreadsheet\IOFactory;
// use Intervention\Imagenes\Image;   // composer require intervention/image
// use Intervention\Image\ImageManagerStatic as Image;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

// use Image;

class ProductosController extends Controller
{
    public function registrar_precio_masivo(Request $request, RegistrarPrecioMasivoAction $action)
    {
        return $action($request->all());
    }

    public function lee_precio(Request $request, LeerPrecioAction $action)
    {
        return $action($request->idprod);
    }

    public function graba_precio(Request $request, GuardarPrecioAction $action)
    {
        return $action($request->all());
    }

    public function regitrar_ventaOnline(Request $request, RegistrarVentaOnlineAction $action)
    {
        return $action($request->all());
    }

    public function consulta_cristal(Request $request, ConsultarStockCristalAction $action)
    {
        return $action($request->codigo);
    }

    public function add_publicaciones(Request $request, CrearPublicacionAction $action)
    {
        return $action($request->all());
    } 

    public function publicaciones()
    {

        // Familias Lo ocupa ventana modal de modificacion de Producto
        $familias = familia::select(DB::raw("CONCAT( Flia_Id ,' - ',Flia_Descripcion) as descri"), 'Flia_Id')->orderBy('Flia_Id', 'ASC')->pluck('descri', 'Flia_Id');
        // Lo ocupa ventana modal de Ventas OnLine
        $sucursales = sucursal::combo(99); // 99 para que no muestra la 99 OnLine

        return view('productos.publicaciones', ['familias' => $familias, 'sucursales' => $sucursales]);

    }

    public function publicaciones2(Request $request, ListarPublicacionesAction $action)
    {
        return $action($request->filtroEstado ?? '', $request->filtroDescri ?? '');
    } 

    public function index()
    {
        // Lista de Productos - Pantalla Principal

        $familias = familia::select(DB::raw("CONCAT( Flia_Id ,' - ',Flia_Descripcion) as descri"), 'Flia_Id')->orderBy('Flia_Id', 'ASC')->where('Flia_estado', '!=', 'I')->pluck('descri', 'Flia_Id');
        $listaMoneda = moneda::comboLista();

        return view('productos.index', ['familias' => $familias, 'listaMoneda' => $listaMoneda]);
    }

    public function lista_auditoria(Request $request, ListarAuditoriaAction $action)
    {
        return $action($request->familia, $request->idprod);
    }

    public function lista_movimientos(Request $request, ListarMovimientosAction $action)
    {
        return $action($request->familia, $request->idprod);
    }

    public function buscar(Request $request)
    {
        // Buscar de la Pantalla Principal

        if ($request->mes_ventas == '') {
            $request->mes_ventas = 2;
        }

        $filtro_fecini = date('Y-m-d');
        $filtro_fecini = date('Y-m-d', strtotime($filtro_fecini.'-'.$request->mes_ventas.' month')).' 00:00:00';

        if ($request->ajax()) {
            $datos = producto::listar($request->filtro_flia, $request->filtro1, 10000, $request->filtro2, $request->filtro3, $request->filtroEstado, $request->filtroMarca);

            // Recorro para Completar los stock de las sucursales
            $datos2 = [];
            foreach ($datos as $row) {

                $row->prod_precio = number_format($row->prod_precio, env('DEC_MONTO'), ',', '.');
                if ($row->prod_marca != 0) {
                    $marca = marca::find($row->prod_marca);
                    $row->prod_marca2 = $marca->nombre;
                } else {
                    $row->prod_marca2 = '';
                }

                if ($request->filtroStock == 'C') {
                    // Dejar solo los casos con Stock
                    if ($row->stock01 == 0 and $row->stock02 == 0) {
                        continue;
                    }
                }
                if ($request->filtroStock == 'S') {
                    // Dejar solo los casos Sin Stock
                    if ($row->stock01 != 0 or $row->stock02 != 0) {
                        continue;
                    }
                }
                /*   lo deje en el select para que sea mas rapido
                          if ($inv = inventario::findCodigo($row->prod_idweb,1)  ){
                            $row->stock01 = $inv->Inv_Stock;
                          }
                          if ($inv = inventario::findCodigo($row->prod_idweb,2)  ){
                            $row->stock02 = $inv->Inv_Stock;
                          }
                */
                // Busco las ultimas ventas realizadas
                $consulta = "SELECT sum(Mov_Cantidad) * -1 as cantidad FROM moviproductos  WHERE    Mov_Familia=? AND Mov_IdProd= ? AND Mov_FecMov >= ?  AND ( Mov_Operacion='V' or Mov_Operacion='R') AND Mov_Sucursal= ?";
                $dat = DB::select($consulta, [$row->prod_familia, $row->prod_id, $filtro_fecini, 1]);
                $row->venta01 = $dat[0]->cantidad;

                $consulta = "SELECT sum(Mov_Cantidad) * -1 as cantidad FROM moviproductos  WHERE    Mov_Familia=? AND Mov_IdProd= ? AND Mov_FecMov >= ?  AND  ( Mov_Operacion='V' or Mov_Operacion='R') AND Mov_Sucursal= ?";
                $dat = DB::select($consulta, [$row->prod_familia, $row->prod_id, $filtro_fecini,  2]);
                $row->venta02 = $dat[0]->cantidad;
                array_push($datos2, $row);
            }

            return response()->json(['results' => $datos2]);
        }  // Fin Ajax
    } // Fin Buscar

    public function consolida_codigo(Request $request, ConsolidarCodigoAction $action)
    {
        return $action($request->all());
    }

    public function genera_pedido(Request $request, GenerarPedidoAction $action)
    {
        return $action($request);
    }

    public function movimientos(Request $request)
    {
        // Pantalla Consulta de Movimentos

        $familias = familia::select(DB::raw("CONCAT( Flia_Id ,' - ',Flia_Descripcion) as descri"), 'Flia_Id')->orderBy('Flia_Id', 'ASC')->pluck('descri', 'Flia_Id');
        $sucursales = sucursal::combo(Auth::user()->sucursal, 'S');

        // Parámetros de filtrado desde GET
        $sucursal = $request->get('sucursal', '0');
        $familia = $request->get('familia', '');
        $operacion = $request->get('operacion', '');
        $id_producto = $request->get('id_producto', '');
        $desc_producto = $request->get('desc_producto', '');
        $cod_cero = $request->get('cod_cero', '');
        $mes = (int) $request->get('mes', 0);
        $anio = (int) $request->get('anio', 0);

        // Cálculo de fechas (cuando se navega desde estadísticas con mes/año)
        $fecha = date('Y-m-d');
        $fecha_fin = date('Y-m-d');

        if ($mes > 0) {
            if ($mes > 12) {
                $fecha = date('Y-m-d', mktime(0, 0, 0, 1, 1, $anio));
                $fecha_fin = date('Y-m-d', mktime(0, 0, 0, 12, 31, $anio));
            } else {
                $diafin = date('d', mktime(0, 0, 0, $mes + 1, 1, $anio) - 1);
                $fecha = date('Y-m-d', mktime(0, 0, 0, $mes, 1, $anio));
                $fecha_fin = date('Y-m-d', mktime(0, 0, 0, $mes, $diafin, $anio));
            }
        }

        return view('productos.movimientos', compact(
            'sucursales', 'familias',
            'sucursal', 'familia', 'operacion', 'id_producto', 'desc_producto', 'cod_cero',
            'fecha', 'fecha_fin'
        ));
    }

    public function buscar_movimientos(Request $request, BuscarMovimientosAction $action)
    {
        return $action($request);
    }

    public function show(Request $request)
    {
        // Tiene que estar, lo utiliza para mostrar el index
        // Se utiliza cuando llama a la ventana de Modificar para traer los datos por Id

        $registro = producto::find($request->id);

        $registro->Prod_Familia = str_replace(' ', '', $registro->Prod_Familia); // Para sacar los espacios, seleccione bien la familia

        // Leo Stock por sucursal
        if (! $inventario = inventario::findCodigo($registro->Prod_idWEB, 1)) {
            $registro->stock1 = 0;
        } else {
            $registro->stock1 = $inventario->Inv_Stock;
        }
        if (! $inventario = inventario::findCodigo($registro->Prod_idWEB, 2)) {
            $registro->stock2 = 0;
        } else {
            $registro->stock2 = $inventario->Inv_Stock;
        }

        $imagenes = '';
        foreach ($registro->images as $image) {
            // $imagenes = '<img src="data:image/jpeg;base64,'. base64_encode(  public_path() . $image->url).'" class="img-responsive img-rounded ">';

            $imagenes .= '<div  class="col-sm-2">';
            $imagenes .= '<img style="width:150px; height:150px;" src="'.asset($image->url).'" class="img-fluid mb-2">';
            $imagenes .= '</div>';

        }

        $lblPrecio = '';
        if ($row = precio::find_producto($request->id)) {
            $lblPrecio = '<b>En Dolares &nbsp;&nbsp;&nbsp;'.
            ' Precio: </b>'.$row->precio.
            '&nbsp;&nbsp;<b>Precio 2: </b>'.$row->precio2.
            ' <b>&nbsp;&nbsp;&nbsp;Costo: </b>'.$row->costo;
        }

        // Buscar datos en tienda online por sku
        $tienda_descripcion = '';
        $tienda_precio = 0;
        if ($prod_tienda = tienda_producto::find($registro->Prod_Id)) {
            $tienda_descripcion = $prod_tienda->descripcion;
            $tienda_precio = $prod_tienda->precio;
        }

        return response()->json([
            'id' => $registro->Prod_idWEB,
            'lblPrecio' => $lblPrecio,
            'imagenes' => $imagenes,
            'tienda_descripcion' => $tienda_descripcion,
            'tienda_precio' => $tienda_precio,
            'result' => $registro,
        ]);

    }

    public function store(Request $request)
    {

        // Boton Aceptar del Alta o Modificacion
        if ($request->operation == 'update') {
            if (! $registro = producto::find($request->id)) {
                abort(402, 'Error: No se encontro el Id:'.$request->id);
            }
            $registro->fill($request->all());
            $registro->Prod_UsuUltMan = 'ModifWEB';

            // Actualiza el Stock
            if (! $inventario = inventario::findCodigo($registro->Prod_idWEB, 1)) {
                $inventario = new inventario;
                $inventario->Inv_idProd = $registro->Prod_idWEB;
                $inventario->Inv_Sucursal = 1;
                $inventario->Inv_Stock = 0;
            }
            if ($inventario->Inv_Stock != $request->stock1) {
                $inventario->Inv_Stock = $request->stock1;
                $inventario->save();
            }

            if (! $inventario = inventario::findCodigo($registro->Prod_idWEB, 2)) {
                $inventario = new inventario;
                $inventario->Inv_idProd = $registro->Prod_idWEB;
                $inventario->Inv_Sucursal = 2;
                $inventario->Inv_Stock = 0;
            }
            if ($inventario->Inv_Stock != $request->stock2) {
                $inventario->Inv_Stock = $request->stock2;
                $inventario->save();
            }

            // Si modifico datos de la tienda lo actualizo
            if ($request->tienda_descripcion != '') {
                // Buscar datos en tienda online por sku
                $prod_tienda = tienda_producto::find($request->Prod_Id);
                if ($request->tienda_precio != $prod_tienda->precio) {
                    $prod_tienda->precio = $request->tienda_precio;
                    $prod_tienda->save();
                    // dd(val($request->tienda_precio), val($prod_tienda->precio) );
                }
            }

        } else { // Alta
            $registro = new producto($request->all());

        }  // Fin Tipo Operacion

        $registro->save();  // SI es alta genera el Id

        // Cargo las Imagenes, si es que selecciono
        $urlimagenes = [];
        if ($request->hasFile('imagenes')) {
            $imagenes = $request->file('imagenes');
            foreach ($imagenes as $imagen) {
                $nombre = time().'_'.$imagen->getClientOriginalName();
                $ruta = public_path().'/imagenes/productos/';
                $image_resize = Image::make($imagen); // Corta la imagen
                $image_resize->resize(530, 591, function ($constraint) {
                    $constraint->aspectRatio(); // Mantiene las proporciones
                    // $constraint->upsize();
                });
                // $image_resize->orientate();
                $image_resize->save($ruta.$nombre);
                $urlimagenes[]['url'] = '/imagenes/productos/'.$nombre;
            }
        }
        $registro->images()->createMany($urlimagenes);

        return response()->json([
            'id' => $registro->Prod_idWEB,
            'ret' => 'Se ha registrado de manera exitosa ! :'.$registro->Prod_Descripcion,
        ]);

    }

    public function planilla_cristales(Request $request, GenerarPlanillaStockAction $action)
    {
        return $action();
    }

    public function cambia_codigo(Request $request, CambiarCodigoAction $action)
    {
        return $action($request->all());
    }

    public function genera_pedidoPRECIOS(Request $request, GenerarPedidoPreciosAction $action)
    {
        return $action($request->all());
    }

    public function GeneroNvoCodigo(Request $request, GenerarNuevoCodigoAction $action)
    {
        return $action($request->familia);
    }

    public function actualiza_precio()
    {

        // Pantalla , Pide archivo Precio de Cristales a procesar
        return view('procesa_archivo', ['titulo' => 'Proceso Lista de Precios Cristales',
            'mensaje' => 'Seleccione Lista de Precios actualizada a Procesar',
            'accion' => 'actualiza_precio_proceso',
            'tipoArchivo' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel']);

    } // Fin

    public function actualiza_precio_proceso(Request $request, ActualizarPrecioExcelAction $action)
    {
        return $action($request);
    }

    public function consultaprecio(Request $request)
    {

        return view('productos.consultaprecio');

    } // Fin Consulta Precio

    public function modificaprecio(Request $request)
    {
        return view('productos.modificaprecio');
    }

    public function buscaproducto(Request $request, BuscarProductoAction $action)
    {
        return $action($request->familia, $request->terms);
    }

    public function consultaprecio2(Request $request, ConsultarPrecioDetalleAction $action)
    {
        return $action($request);
    }

    public function consultaprecioventa(Request $request, ConsultarPrecioVentaAction $action)
    {
        return $action();
    }

    public function modificaprecio2(Request $request, ModificarPrecioAction $action)
    {
        return $action($request->all());
    }


} // Fin de la Clase
