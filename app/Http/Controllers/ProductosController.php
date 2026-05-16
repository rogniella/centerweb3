<?php namespace App\Http\Controllers;

/*
    Para manejo de zip Con la vs nueva usamos una extension de PHP
       A partir de PHP 8.2.0, la DLL php_zip.dll debe estar habilitada en php.ini . Anteriormente, esta extensión estaba integrada.
    ya no //use Zipper;
*/

use ZipArchive; // Extension PHP 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\Models\cotizacion;  
use App\Models\clases\comprobante;

use App\Models\producto;  
use App\Models\familia;  
use App\Models\inventario;  
use App\Models\marca;
use App\Models\publicacion;
use App\Models\precio;
use App\Models\sucursal;  
use App\Models\moneda;  
use App\Models\tienda_producto;

use Codexshaper\WooCommerce\Facades\Product;

//Ver, por falla en  $sheet->getCell($letraVal.$i)->getCalculatedValue()  tuve que copiar la carpeta que me bajo en vendor, por la que funcionaba Ok , esta en el d:/tools/web/php y excel/ zip . 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Illuminate\Support\Facades\File;

// use Intervention\Imagenes\Image;   // composer require intervention/image
// use Intervention\Image\ImageManagerStatic as Image;
use Intervention\Image\Facades\Image;

//use Image;

class ProductosController extends Controller
  {
    

    public function registrar_precio_masivo (Request $request)  {
      

        $datos = producto::listar($request->filtro_flia, $request->filtro1,10000, $request->filtro2 , $request->filtro3, $request->filtroEstado , $request->filtroMarca );

        // Recorro para Completar los stock de las sucursales
        $cantidad = 0;
        foreach ($datos as $elem) {
          if ( $elem->prod_id != '9999' and  $elem->prod_id != '0' ) {
            $oproducto  = Producto::findCodigo($elem->prod_familia,$elem->prod_id );
            // Aplica a Precios o Ambos
            if ( $request->precio_aplica == 1 or $request->precio_aplica == 3 ) { 
              $oproducto->Prod_Precio =  $this->calculo_aumento( $oproducto->Prod_Precio ,$request->precio_recargo,$request->precio_tipo,$request->precio_redondeo );
              $oproducto->Prod_Precio2 =  round( $oproducto->Prod_Precio * 0.9 , 2) ; // - 10 % 
//              $oproducto->Prod_Precio2 =  $this->calculo_aumento( $oproducto->Prod_Precio2 ,$request->precio_recargo,$request->precio_tipo,$request->precio_redondeo );
            }
            // Aplica a Costos o Ambos
            if ( $request->precio_aplica == 2 or $request->precio_aplica == 3 ) { 
              $oproducto->Prod_Costo =  $this->calculo_aumento( $oproducto->Prod_Costo ,$request->precio_recargo,$request->precio_tipo,$request->precio_redondeo );
            }
            $oproducto->Prod_UsuUltMan = 'LWebPrecio';
            $oproducto->actualizar();
            $cantidad ++;
          }
        }

        return response()->json([ 'cantidad' => $cantidad ]);

  } // Fin registrar_precio_masivo

  private function calculo_aumento ( $valor, $recargo, $tipo , $redondeo ) {

    $recargo = floatval($recargo);
    switch ( $tipo ) {
      case "1": // Incremento Porcentual
          $factor = 1 + ( $recargo / 100);
        //  dd ($factor , $valor, $recargo, $tipo , $redondeo );
        return  redondear_a_10( $valor * $factor  , $redondeo );
      case "2": // Incremento Fijo
        return  redondear_a_10( $valor +  $recargo ,   $redondeo);
      case "3": // Valor Fijo
        return   $recargo;
      case "4": //  Valor Minimo
          if ( $recargo > $valor ) {
            return   $recargo;
          }else{
            return   $valor;
          }
    }    
  }

  public function lee_precio (Request $request)  {

    if ( !  $datos = precio::find_producto( $request->idprod) ) { 
       return response()->json([
          'idLista' => '',  
          'costo' => 0,  
          'precio' => 0,  
          'precio2' => 0  
       ]);
    }
    return response()->json(  $datos );
  
  }
  
  public function graba_precio (Request $request)  {

      if ( !  $row  = precio::find_producto( $request->idprod) ) { 
         $row = new precio();
         $row->idWEB_Prod = $request->idprod;         
      }
      
      $row->precio = $request->precio;
      $row->precio2 = $request->precio2;
      $row->costo = $request->costo;
      $row->idlista = $request->idlista;
      if ( ! $row->save() ) {
            $ret =  " Error al actualizar tabla Precios " ;
            return  $ret;
      };  

      //  Para consultar Cotizacion  
      $cotiza = 0;
      $aux =  cotizacion::mtoEnPesos( $request->idlista ,100,'',$cotiza);

      if  ( ! $producto  = Producto::find( $request->idprod ) ) {
            $ret =  " Error al leer tabla Productos Id:" . $request->idprod  ;
            return  $ret;
      } 

      $producto->Prod_Precio = $request->precio * $cotiza;
      $producto->Prod_Precio2 = $request->precio2 * $cotiza;
      $producto->Prod_Costo = $request->costo * $cotiza;
      $producto->Prod_UsuUltMan = 'ADM_Precio';
      $producto->actualizar() ;

      return response()->json( [ 'msg' => 'Se actualizo Precios con Éxito!!'  ] );
  }

  public function regitrar_ventaOnline(Request $request)
  {
      // Registra Venta OnLine
      // Saca del stock de la sucursal origen del producto y marca la venta

    if ( ! $row = Publicacion::find($request->idweb) ) {
          $ret =  " Error al buscar en tabla Publicaciones Id:" . $request->idweb ;
          return  $ret;
    }

    switch ( $request->accion ) {
      case 'P': // Pausar Venta
        $row->observ = $request->observ;
        $row->estado = "P"; // Pausada
        if ( ! $row->save() ) {
            $ret =  " Error al actualizar en tabla Publicaciones " ;
            return  $ret;
        };  
        return response()->json( [ 'msg' => 'Publicacion Pausada con Éxito!!'  ] );
      case 'V': // Venta
        DB::beginTransaction();
        $row->precio_venta = $request->precio;
        if ($row->precio_venta == '') { $row->precio_venta = 0;}
        $row->observ = $request->observ;
        $row->estado = "V"; // Vendido
        if ( ! $row->save() ) {
            $ret =  " Error al actualizar en tabla Publicaciones " ;
            DB::rollBack();
            return  $ret;
        };  

        $sucursalOnline = 99;
        // Saco del stock sucursal origen  $request->venta_sucursal
        // Actualiza el stock
        if ( ! $producto  = Producto::find($row->idWEB_prod) ) {
            $ret =  " Error al buscar en tabla Productos Id:" . $row->idWEB_prod ;
            DB::rollBack();
            return  $ret;
        }
        
        $asunto =   'Envío VtaOnline:' . $request->idweb;
        $producto->Prod_UsuUltMan  = "Vta.Online";
        // Inserta reg en tabla de Movimientos de Producto  y Actualiza Stock
        // Salida de la Suc
        $producto->addMovimiento('I',1,$request->precio,
               $request->idweb , $sucursalOnline,'Pasa para Vta Online desde Suc:' . $request->sucursal ,'',$request->sucursal);
        // Venta de la Suc Online
        $producto->addMovimiento('V_OnLine',1,$request->precio,
               $request->idweb , 0,'Venta con Producto de la Suc:' . $request->sucursal ,'', $sucursalOnline);  
        DB::commit();
        return response()->json( [ 'msg' => 'Venta OnLine Registrada con Éxito!!'  ] );

    } // end Switch  

  }

  public function consulta_cristal (Request $request)  {

    // Es Utilizada como llamada API desde la aplicación Vb
    //http://localhost/centerweb2/public/api/consulta_cristal?codigo=OB-100
    // https://admin.centerfotooptica.com.ar/api/consulta_cristal?codigo=OB%2B100
    //https://admin.centerfotooptica.com.ar/api/consulta_cristal?codigo=OB-200100
    if  ( ! $oproducto  = Producto::findCodigo("CRI",$request->codigo ) ) {
      $stock01 = "ERR1";
      $stock02 = "ERR1";
     // displaylog( "Error: Al buscar Cod Cristal:" + $request->codigo );
      return response()->json( [ 'stock01' => $stock01,'stock02' => $stock02  ] );
    } 
    $stock01 = 0; $stock02 = 0;
    if ($inv = inventario::findCodigo($oproducto->Prod_idWEB,1)  ){
      $stock01 = $inv->Inv_Stock;
    }
    if ($inv = inventario::findCodigo($oproducto->Prod_idWEB,2)  ){
      $stock02 = $inv->Inv_Stock;
    }
    //DD ($stock01, $stock02 , $oproducto->Prod_idWEB, $oproducto) ;
    $stock01 = str_pad($stock01 ,4," ", STR_PAD_LEFT);
    $stock02 = str_pad($stock02 ,4," ", STR_PAD_LEFT);
    return response()->json( [ 'stock01' => $stock01,'stock02' => $stock02  ] );

  } // fin consulta_cristal
  
  public function add_publicaciones (Request $request)  {

    $row  = new Publicacion;
    $row->idWEB_Prod = $request->idwebProd;
    $row->cantidad = $request->cantidad;
    $row->precio_venta = $request->precio;
    if ($row->precio_venta == '') { $row->precio_venta = 0;}
    $row->observ = $request->observ;
    $row->estado = "A"; // Activo
    if ( ! $row->save() ) {
        $ret =  " Error al actualizar en tabla Publicaciones " ;
        return  $ret;
    };  

    if ( ! $producto  = Producto::find($request->idwebProd) ) {
        $ret =  " Error al buscar en tabla Productos Id:" . $request->idwebProd ;
        return  $ret;
    }

    if (Auth::user() )  $producto->Prod_UsuUltMan =  Auth::user()->name;   
    $producto->insertHistoria( 'Publicación OnLine','','');

  return response()->json( [ 'msg' => 'Ok'  ] );

} // fin add_publicaciones




  public function publicaciones()
  {

    // Familias Lo ocupa ventana modal de modificacion de Producto 
    $familias = familia::select(DB::raw("CONCAT( Flia_Id ,' - ',Flia_Descripcion) as descri"),'Flia_Id')->orderBy('Flia_Id', 'ASC')->pluck( 'descri','Flia_Id'); 
    // Lo ocupa ventana modal de Ventas OnLine
    $sucursales = sucursal::combo(99); // 99 para que no muestra la 99 OnLine
    return view('productos.publicaciones', [ 'familias' => $familias,'sucursales' => $sucursales  ]  );

  }


  public function publicaciones2 (Request $request)  {

    // Buscar datos

      $datos = publicacion::listar($request->filtroEstado, $request->filtroDescri);

      $array_prod = tienda_producto::listar($request->filtroDescri);

      // Para buacar en array , mucho mas rapido que en bd
      $sku = array_column($array_prod, 'sku');
      
      // Recorro para Completar los stock de las sucursales
      foreach ($datos as $row) {
           
          // Busco en array , mucho mas rapido que en bd
          $key = array_search( $row->prod_id, $sku);
          if ( is_numeric( $key) ) {
              $row->tienda_name = $array_prod [$key]->name  ;
              $row->tienda_precio = $array_prod [$key]->regular_price;
              IF ($array_prod [$key]->stock != '') {
                  $row->observ = $row->observ . ' En Tienda:' . $array_prod [$key]->stock;
              }
          }          
          /*  Uso con array - mucho mas rapido que API
          if ($product = Product::where('sku','=', $row->prod_id )->first() ) {
            if (isset($product['regular_price'])) {
              displaylog ($product['name'] );
              $row->tienda_name = $product['name'];
              $row->tienda_precio = $product['regular_price'];
            }
          }          
          */  
      }

      return response()->json([ 'results' => $datos , 'tienda' => $array_prod ]);

  } // Fin Publicaciones2


  public function index()
  {
      // Lista de Productos - Pantalla Principal

      $familias = familia::select(DB::raw("CONCAT( Flia_Id ,' - ',Flia_Descripcion) as descri"),'Flia_Id')->orderBy('Flia_Id', 'ASC')->where( 'Flia_estado','!=','I')->pluck( 'descri','Flia_Id'); 
      $listaMoneda = moneda::comboLista();

      return view('productos.index' , [ 'familias' => $familias , 'listaMoneda' => $listaMoneda ] );
  }

  public function lista_auditoria (Request $request)  {
      // Usa Pestaña del Adm de Productos
      $datos = producto::listar_auditoria( $request->familia , $request->idprod);
      return response()->json([ 'results' => $datos ]);
  }

  public function lista_movimientos (Request $request)  {
      // Usa Pestaña del Adm de Productos
      $datos = producto::listar_movimientos( $request->familia , $request->idprod);
   //   dd ($datos);
      return response()->json([ 'results' => $datos ]);
  }

  public function buscar (Request $request)  {
      // Buscar de la Pantalla Principal

      if ($request->mes_ventas == '') {$request->mes_ventas = 2;}

      $filtro_fecini =  date('Y-m-d');
      $filtro_fecini = date("Y-m-d",strtotime($filtro_fecini ."-" . $request->mes_ventas . " month")) .  " 00:00:00" ;

      if($request->ajax() ) {
        $datos = producto::listar($request->filtro_flia, $request->filtro1,10000, $request->filtro2 , $request->filtro3, $request->filtroEstado , $request->filtroMarca );

        // Recorro para Completar los stock de las sucursales
        $datos2 = [];
        foreach ($datos as $row) {
           
          $row->prod_precio = number_format($row->prod_precio ,env('DEC_MONTO'),",",".");
          if ( $row->prod_marca != 0 ){
            $marca = marca::find($row->prod_marca);
            $row->prod_marca2 = $marca->nombre;
          }else{
            $row->prod_marca2 = '';
          }  

          if ($request->filtroStock == "C") {
            // Dejar solo los casos con Stock  
            if ( $row->stock01 == 0 and  $row->stock02 == 0 ) continue;  
          }
          if ($request->filtroStock == "S") {
            // Dejar solo los casos Sin Stock  
            if ( $row->stock01 != 0 or  $row->stock02 != 0 ) continue;  
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
          $dat = DB::select($consulta, [$row->prod_familia, $row->prod_id,$filtro_fecini, 1 ] );
          $row->venta01 = $dat[0]->cantidad;

          $consulta = "SELECT sum(Mov_Cantidad) * -1 as cantidad FROM moviproductos  WHERE    Mov_Familia=? AND Mov_IdProd= ? AND Mov_FecMov >= ?  AND  ( Mov_Operacion='V' or Mov_Operacion='R') AND Mov_Sucursal= ?";
          $dat = DB::select($consulta, [$row->prod_familia, $row->prod_id,$filtro_fecini,  2 ]);
          $row->venta02 = $dat[0]->cantidad;
          array_push($datos2,$row);
        }

        return response()->json([ 'results' => $datos2 ]);
      }  // Fin Ajax
  } // Fin Buscar


  public function consolida_codigo (Request $request)  {
      // Boton de Juntar Codigos en uno
      // Recorro todos los productos seleccionados

      $cantidad = 0;
      $cod_minimo = 99999;
     // dd($request->selectedRows);
      foreach ($request->selectedRows as $row) {
       //  dd($row , $row["prod_familia"] ,  $row["prod_id"] , $request->cod_destino );
       //  dd($row  );
       if ($row < $cod_minimo) {
            $cod_minimo = $row;
        }
        $res = producto::reemplaza_codigo($request->familia, $row, $request->cod_destino );
        IF ( $res != ""  ){
               dd($res);
        }
        $cantidad = $cantidad + 1;  
      }  

    // Dependiendo de la Famila Genero Nvo Codigo Libre
    $consulta = "SELECT Flia_MaxId FROM familias  WHERE    Flia_Id= ?";
    $datos = DB::select($consulta, [$request->familia] );
    $naux = $datos[0]->Flia_MaxId;
    if($cod_minimo < $naux  ){
      // Actualiza el Max codigo
      $consulta = "UPDATE familias SET Flia_MaxId = ?   WHERE    Flia_Id= ?";
      $datos = DB::update($consulta, [ $cod_minimo - 1 , $request->familia] );
      displaylog("Actualizo el Codigo Minimo de la Familia" );
    }

      return response()->json([ 'results' => 'Ok Productos Unificados:' . $cantidad . ' Min.Codigo:' . $cod_minimo  ]);


  } // Fin Juntar Codigos

  public function genera_pedido (Request $request)  {

    // Boton Genera Pedidos 

    $filename =  storage_path() . "/template/PlanillaCristales.xlsx";
    $filename2 =  storage_path() . "/template/PlanillaPedidos.xlsx";

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet = $reader->load($filename);
    $sheet = $spreadsheet->setActiveSheetIndex(0); // Organico Blanco 

    $reader3 = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet3 = $reader3->load($filename);
    $sheet3 = $spreadsheet3->setActiveSheetIndex(0); 


    $reader2 = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet2 = $reader2->load($filename2);
    $sheet2 = $spreadsheet2->setActiveSheetIndex(0); // Organico Blanco 
    $fila = 3; // Titulos 

    if ($request->mes_ventas == '') {$request->mes_ventas = 2;}

    $filtro_fecini =  date('Y-m-d');
    $filtro_fecini = date("Y-m-d",strtotime($filtro_fecini ."-" . $request->mes_ventas . " month")) .  " 00:00:00" ;

    if($request->ajax() ) {
      $datos = producto::listar($request->filtro_flia, $request->filtro1,10000, $request->filtro2 , $request->filtro3, $request->filtroEstado , $request->filtroMarca );

      // Recorro para Completar los stock de las sucursales
      foreach ($datos as $row) {
         
        if ( $row->prod_marca != 0 ){
          $marca = marca::find($row->prod_marca);
          $row->prod_marca2 = $marca->nombre;
        }else{
          $row->prod_marca2 = '';
        }  

        // Busco las ultimas ventas realizadas
        $consulta = "SELECT sum(Mov_Cantidad) * -1 as cantidad FROM moviproductos  WHERE    Mov_Familia=? AND Mov_IdProd= ? AND Mov_FecMov >= ?  AND  (Mov_Operacion='V' OR Mov_Operacion='R') AND Mov_Sucursal= ?";
        $dat = DB::select($consulta, [$row->prod_familia, $row->prod_id,$filtro_fecini, 1 ] );
        $row->venta01 = $dat[0]->cantidad;

        $consulta = "SELECT sum(Mov_Cantidad) * -1 as cantidad FROM moviproductos  WHERE    Mov_Familia=? AND Mov_IdProd= ? AND Mov_FecMov >= ?  AND (Mov_Operacion='V' OR Mov_Operacion='R')  AND Mov_Sucursal= ?";
        $dat = DB::select($consulta, [$row->prod_familia, $row->prod_id,$filtro_fecini,  2 ]);
        $row->venta02 = $dat[0]->cantidad;

        // Listo las Ventas
        if (trim($row->prod_familia) == 'CRI') {
          $celda =  $row->prod_usualta ;
          $sheet3->setCellValue($celda, $row->venta01 + $row->venta02);
        }

        // Veo si hay que pedir  
        $stock_total =  $row->stock01 + $row->stock02;  
        $ventas_total =    $row->venta01 + $row->venta02;
        $diferencia = $ventas_total - $stock_total ; 

        if( $diferencia > 0 ) {
            displaylog($diferencia . ' ' . $row->prod_familia );
            if (trim($row->prod_familia) == 'CRI') {
              $celda =  $row->prod_usualta ;
              $sheet->setCellValue($celda, $diferencia);
              displaylog($diferencia . ' planilla ' . $celda );
            }
            $fila = $fila + 1;
            $celda = "A" . strval ($fila);
            $sheet2->setCellValue($celda, $row->prod_familia . '-'. $row->prod_id);            
            $celda = "B" . strval ($fila);
            $sheet2->setCellValue($celda, $row->prod_descripcion);            
            $celda = "C" . strval ($fila);
            displaylog($celda);
            $sheet2->setCellValue($celda, $diferencia);            
        }
      }

//      return response()->json([ 'results' => $datos ]);

      $fecha = fechahoy();
      $writer = new Xlsx($spreadsheet);
      $filename =   "/salidas/PedidoCristales_" . $fecha   . ".xlsx";
      $filenameSalida =  public_path() . $filename ;
      $writer->save($filenameSalida);
      $file1 =   "PedidoCristales_" . $fecha   . ".xlsx";
      $path1 =   "salidas/" .  $file1;

      $writer2 = new Xlsx($spreadsheet2);
      $filename =   "/salidas/PedidoProveedor_" . $fecha   . ".xlsx";
      $filenameSalida2 =  public_path() . $filename ;
      $writer2->save($filenameSalida2);

      $path2 =   "salidas/PedidoProveedor_" . $fecha   . ".xlsx";
      $file2 =   "PedidoProveedor_" . $fecha   . ".xlsx";

      $writer3 = new Xlsx($spreadsheet3);
      $filename =   "/salidas/VentasCristales_" . $fecha   . ".xlsx";
      $filenameSalida3 =  public_path() . $filename ;
      $writer3->save($filenameSalida3);
      $file3 =   "VentasCristales_" . $fecha   . ".xlsx";
      $path3 =   "salidas/" .  $file3;

      $filezipname = "Pedido_" . $fecha  . ".zip";
      $filezip = "/salidas/Pedido_" . $fecha  . ".zip";
      $filenameSalidaZip =  public_path() .  $filezip ;


      /* Le indicamos en que carpeta queremos que se genere el zip y los comprimimos*/
      //Zipper::make($filenameSalidaZip)->add($files)->close();

      $zip = new ZipArchive();
      if ($zip->open($filenameSalidaZip, ZipArchive::CREATE)!==TRUE) {
           dd("cannot open <$filenameSalidaZip>\n" . "estado:" . $zip->status );
      }
      $zip->addFile(public_path ($path3), $file3);
      $zip->addFile(public_path ($path2), $file2);
      $zip->addFile(public_path ($path1), $file1);
  //    echo "numficheros: " . $zip->numFiles . "\n";
  //    echo "estado:" . $zip->status . "\n";
      $zip->close();

/* ver
          // Set Header
          $headers = array(
            'Content-Type' => 'application/octet-stream',
        );
        $filetopath= public_path() . $filezip;
        // Create Download Response
        if(file_exists($filetopath)){
            return response()->download($filetopath,$filezipname,$headers);
        }
*/


      $file_redirec =  asset('')  . $filezip;

      return response()->json([ 'redirec' => $file_redirec ]);

      return view('mensaje', ['titulo' => "CONFIRMACIÓN",
                           'mensaje' => 'Se generaron Planillas de Pedidos', 
                           'pdf' => $file_redirec ] );



    }  // Fin Ajax
} // Fin Genera Pedido



  public function edit(Request $request)
  {

      // Pantalla No Modal de Informacion
      $producto   = producto::find($request->id);

      $marcas = DB::select("SELECT  id , nombre  FROM marcas where familia = '". $producto->Prod_Familia . "' and estado <>'I'" ); 

     // dd($marcas);

      return view('productos.edit' ,  compact('producto','marcas') );
  
  }



  public function movimientos()
  {
      // Pantalla Consulta de Movimentos

      $familias = familia::select(DB::raw("CONCAT( Flia_Id ,' - ',Flia_Descripcion) as descri"),'Flia_Id')->orderBy('Flia_Id', 'ASC')->pluck( 'descri','Flia_Id'); 
      $sucursales = sucursal::combo(Auth::user()->sucursal, 'S'); //Incluye todas


      return view('productos.movimientos' , [ 'sucursales' => $sucursales , 'familias' => $familias ] );
  }

  public function buscar_movimientos (Request $request)  {

      // Boton Actualizar Pantalla Consulta de Movimentos
   //dd( $request->cod_cero);
      $datos = producto::buscar_movimientos(  $request->fecha, $request->fechafin,$request->sucursal, $request->tipo_operacion ,$request->familia , $request->idprod  , $request->desc_producto , $request->cod_cero);
    

      return response()->json([ 'results' => $datos ]);
  
  }


  public function show(Request $request)
  {
    // Tiene que estar, lo utiliza para mostrar el index
    // Se utiliza cuando llama a la ventana de Modificar para traer los datos por Id 

    $registro   = producto::find($request->id);

    $registro->Prod_Familia =  str_replace(" ","",$registro->Prod_Familia); // Para sacar los espacios, seleccione bien la familia 

    //Leo Stock por sucursal
    if (! $inventario = Inventario::findCodigo( $registro->Prod_idWEB ,1)) { 
      $registro->stock1 = 0;
    }else{  
      $registro->stock1 = $inventario->Inv_Stock;
    }  
    if (! $inventario = Inventario::findCodigo( $registro->Prod_idWEB ,2)) { 
      $registro->stock2 = 0;
    }else{  
      $registro->stock2 = $inventario->Inv_Stock;
    }  

    $imagenes = "";
    foreach ($registro->images as $image) {
      // $imagenes = '<img src="data:image/jpeg;base64,'. base64_encode(  public_path() . $image->url).'" class="img-responsive img-rounded ">';

       $imagenes .= '<div  class="col-sm-2">';
       $imagenes .= '<img style="width:150px; height:150px;" src="' .   asset($image->url) .'" class="img-fluid mb-2">';       
       $imagenes .= '</div>';

    }   

    $lblPrecio = '' ;
    if (   $row  = precio::find_producto( $request->id) ) { 
        $lblPrecio = '<b>En Dolares &nbsp;&nbsp;&nbsp;' . 
        ' Precio: </b>' . $row->precio .
        '&nbsp;&nbsp;<b>Precio 2: </b>' . $row->precio2 .
        ' <b>&nbsp;&nbsp;&nbsp;Costo: </b>' . $row->costo ;
    }

    // Buscar datos en tienda online por sku
    $tienda_descripcion = '';
    $tienda_precio = 0 ;
    if ( $prod_tienda = tienda_producto::find($registro->Prod_Id) ) {
       $tienda_descripcion = $prod_tienda->descripcion;
       $tienda_precio  = $prod_tienda->precio; 
    }


    return response()->json([
      'id' => $registro->Prod_idWEB,  
      'lblPrecio' => $lblPrecio,  
      'imagenes' => $imagenes, 
      'tienda_descripcion' => $tienda_descripcion,  
      'tienda_precio' => $tienda_precio  ,
      'result' => $registro  
    ]);

  }


  public function store (Request $request)
  {
     
    // Boton Aceptar del Alta o Modificacion   
    if  ( $request->operation == 'update' ) {
      if  ( ! $registro  = producto::find($request->id) ) {
        abort(402, 'Error: No se encontro el Id:' . $request->id); 
      };
      $registro->fill($request->all());
      $registro->Prod_UsuUltMan = 'ModifWEB';  

        // Actualiza el Stock 
        if (! $inventario = Inventario::findCodigo( $registro->Prod_idWEB ,1)) { 
          $inventario  = new Inventario;
          $inventario->Inv_idProd = $registro->Prod_idWEB;
          $inventario->Inv_Sucursal = 1;
          $inventario->Inv_Stock = 0;
        }  
        if ($inventario->Inv_Stock != $request->stock1) {
          $inventario->Inv_Stock = $request->stock1;
          $inventario->save();
        }

        if (! $inventario = Inventario::findCodigo( $registro->Prod_idWEB ,2)) { 
          $inventario  = new Inventario;
          $inventario->Inv_idProd = $registro->Prod_idWEB;
          $inventario->Inv_Sucursal = 2;
          $inventario->Inv_Stock = 0;
        }  
        if ($inventario->Inv_Stock != $request->stock2) {
          $inventario->Inv_Stock = $request->stock2;
          $inventario->save();
        }


      // Si modifico datos de la tienda lo actualizo
      if ($request->tienda_descripcion != '' ) {
          // Buscar datos en tienda online por sku
          $prod_tienda = tienda_producto::find($request->Prod_Id);
          if ( $request->tienda_precio !=  $prod_tienda->precio ){
            $prod_tienda->precio = $request->tienda_precio;
            $prod_tienda->save();           
            //dd(val($request->tienda_precio), val($prod_tienda->precio) );
          }
      }


    }else{ // Alta
      $registro = new producto($request->all());

    }  //Fin Tipo Operacion

    $registro->save();  // SI es alta genera el Id      

    // Cargo las Imagenes, si es que selecciono  
    $urlimagenes = [];
    if ($request->hasFile('imagenes')) {
        $imagenes = $request->file('imagenes'); 
        foreach ($imagenes as $imagen) {
            $nombre = time().'_'.$imagen->getClientOriginalName();
            $ruta = public_path().'/imagenes/productos/';
            $image_resize = Image::make( $imagen ); //Corta la imagen
            $image_resize->resize(530, 591, function($constraint) {
            $constraint->aspectRatio(); // Mantiene las proporciones
                 // $constraint->upsize();
            });
            // $image_resize->orientate();
            $image_resize->save(  $ruta . $nombre );
            $urlimagenes[]['url'] = '/imagenes/productos/'.$nombre;
        }
    }
    $registro->images()->createMany($urlimagenes);


    return response()->json([
        'id' =>  $registro->Prod_idWEB  ,
        'ret' => "Se ha registrado de manera exitosa ! :" . $registro->Prod_Descripcion
    ]);

  }

  public function planilla_cristales (Request $request)  {

      $file1 = $this->planillaCristales(1); // Planilla Paso de los Libres
      $file2 = $this->planillaCristales(2); // Planilla Mercedes
    
      $fecha = fechahoy();

      $filezipname = "StockCristales_" . $fecha  . ".zip";
      $filezip = "/salidas/" . $filezipname ;
      $filenameSalidaZip =  public_path() .  $filezip ;


      /* Le indicamos en que carpeta queremos que se genere el zip y los comprimimos*/
      //Zipper::make($filenameSalida)->add($files)->close();

      $zip = new ZipArchive();
      if ($zip->open($filenameSalidaZip, ZipArchive::CREATE)!==TRUE) {
           dd("cannot open <$filenameSalidaZip>\n" . "estado:" . $zip->status );
      }
      $path1 =   "salidas/" .  $file1;
      $path2 =   "salidas/" .  $file2;
      $zip->addFile(public_path ($path2), $file2);
      $zip->addFile(public_path ($path1), $file1);
      $zip->close();            
      
      /* Por último, si queremos descarlos, indicaremos la ruta del archiv, su nombre
        y lo descargaremos*/
      //  return response()->download($filenameSalida);

      $file_redirec =  asset('')  . $filezip;
      return view('mensaje', ['titulo' => "CONFIRMACIÓN",
                           'mensaje' => 'Se generaron Planillas de Stock Cristales por Sucursal', 
                           'pdf' => $file_redirec ] );

  }

  private function planillaCristales ( $sucursal)  {

    // Proceso auxiliar para LISTAR STOCK DE CRISTALES

    $filename =  storage_path() . "/template/PlanillaCristales.xlsx";

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet = $reader->load($filename);
    $sheet = $spreadsheet->setActiveSheetIndex(0); // Organico Blanco 

    $codmaterialAnterior = "";


    $celda =  "R2";
    $valor =  fechahorahoy();
    $sheet->setCellValue($celda, $valor);
    $casos = 0;  
    $datos = producto::listar('CRI', '',1600, '' , '');
    foreach ($datos as $elem) {
      if ( $elem->prod_id != '9999' and  $elem->prod_id != '0'  and $elem->prod_usualta != '') {
        $codmaterial = substr($elem->prod_id, 0,2);
        if( $codmaterial != $codmaterialAnterior) {
          if( $codmaterial == 'OB') {
            $sheet = $spreadsheet->setActiveSheetIndex(0); // Organico Blanco 
          }  
          if( $codmaterial == 'OR') {
            $sheet = $spreadsheet->setActiveSheetIndex(1); // Organico Blanco AR 
          }  
          if( $codmaterial == 'OA') {
            $sheet = $spreadsheet->setActiveSheetIndex(2); // Organico Blue Cut 
          }  
          $codmaterialAnterior = $codmaterial;
        }
    
            $celda =  $elem->prod_usualta ;
            $valor = 0;
            if ($inv = inventario::findCodigo($elem->prod_idweb, $sucursal )  ){
               $valor = $inv->Inv_Stock;
            }
            try { 
              $sheet->setCellValue($celda, $valor);
            } catch (\Exception $e) {    
              displaylog ("Error en producto " . $elem->prod_id . 'Celda' . $elem->prod_usualta .  " Error:" . $e );
            }

            $casos ++;
      }
    }

    $writer = new Xlsx($spreadsheet);
    $file =  "StockCristales_" . $sucursal  . ".xlsx";
    $filenameSalida =  public_path() . "/salidas/" . $file;
    $writer->save($filenameSalida);

    return  $file;

  } // Fin Planilla Sucursal


  public function cambia_codigo (Request $request)  {
    /* Para Codigos  Masivamente por:
     Familia
     filtro1 =  descripcion
     filtro2 =  monto
     filtro3 =  ultact
     Estado
    */
    $cantidad =0;
    $datos = producto::listar($request->filtro_flia, $request->filtro1,10000, $request->filtro2, $request->filtro3 ,  $request->filtroEstado );
    foreach ($datos as $elem) {
      if ( $elem->prod_id != '9999' and  $elem->prod_id != '0' ) {
        //No tomo los ya convertidos
        /* Para revertir  
        if ( $elem->prod_id < 9999 ) { 
          $res = producto::cambia_codigo($request->filtro_flia, $elem->prod_id, 'A' . $elem->prod_id);
          $cantidad = $cantidad + 1;  
        }
         */
        if ( substr( $elem->prod_id , 0 , 1) == 'A' ) { 
        //  dd( $elem->prod_id , substr( $elem->prod_id , 1,4 ) );
          $res = producto::cambia_codigo($request->filtro_flia, $elem->prod_id, substr( $elem->prod_id , 1,4 ) );
          $cantidad = $cantidad + 1;  
        }
   
      }
    }
    return response()->json([ 'results' => 'Ok Productos Convertidos:' . $cantidad  ]);

  } // Fin cambia_codigo


  public function genera_pedidoPRECIOS (Request $request)  {

    // Boton Cambia precio Proceso auxiliar para cambiar masivamente precios
    $casos = 0;  
    $datos = producto::listar($request->filtro_flia, $request->filtro1,10000, $request->filtro2 , $request->filtro3, $request->filtroEstado , $request->filtroMarca );
    foreach ($datos as $elem) {
      if ( $elem->prod_id != '9999' and  $elem->prod_id != '0' ) {
            $oproducto  = Producto::findCodigo($elem->prod_familia,$elem->prod_id );
              $oproducto->Prod_Precio = redondear_a_10( $oproducto->Prod_Precio * 1.10 );
              $oproducto->Prod_Precio2 = redondear_a_10($oproducto->Prod_Precio2 * 1.10);
               
            $oproducto->Prod_UsuUltMan = 'LWebPrecio';
            $casos ++;
            $oproducto->actualizar();
      }
    }
    return response()->json([ 'results' => 'Aumento 10%  Casos:' . $casos  ]);

  } // Fin cambia_codigo


  public function GeneroNvoCodigo (Request $request)  {
   
    // SE UTILIZA PARA LAS FAMILIAS QUE CODIFICAN numericamente,  Busca el Proximo Libre  
    $NvoCodigo = Producto::generarNvoCodigo($_GET["familia"]);

    $res = [
      'NvoCodigo' => $NvoCodigo         
    ];

    return response()->json($res);

  } // Fin
 

  public function actualiza_precio ()  {

      // Pantalla , Pide archivo Precio de Cristales a procesar
     return view('procesa_archivo' ,  ['titulo' => "Proceso Lista de Precios Cristales",
                                       'mensaje' => "Seleccione Lista de Precios actualizada a Procesar",
                                       'accion' =>  "actualiza_precio_proceso",
                                       'tipoArchivo' => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"] );

  } // Fin

  public function actualiza_precio_proceso (Request $request)  {

    // Tomo el archivo elegido y actualiza tabla Productos

    DB::beginTransaction();

    $filename = $request->file("nombre_archivo");

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet = $reader->load($filename);

    $detalle  = []; //Informacion de Salida

    $detalle[] = "Usar https://cloudconvert.com/xlsx-to-jpg   para convertir a Imagen";
  
    $detalle[] = "Hoja 1:";     
    $sheet = $spreadsheet->setActiveSheetIndex(0); 


  // Las columnas que tiene los codigos        
  for ($j = 1; $j <= 3; $j++) {
   switch ($j) {
      case '1': // Forte
          $letraCod = "Q";
          $letraVal = "B";
          $letraCos = "G";
          break;
      case '2': // Trio
          $letraCod = "R";
          $letraVal = "C";
          $letraCos = "H";
          break;
      case '3': // Sin AR 
          $letraCod = "S";
          $letraVal = "D";
          $letraCos = "I";
          break;       
    } // fin switch 
    $this->proceso_columna($detalle,$sheet,5,150,$letraCod,$letraVal,$letraCos);
  }  //  End For j     


  $detalle[] = "Hoja 2 Lentes de Contacto: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(1); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "Q";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "R";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,34,$letraCod,$letraVal,$letraCos,'LC');

    }  //  End For
  


    $detalle[] = "Hoja 3 X Serie: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(2); // de varilux
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': // XClusive
            $letraCod = "N";
            $letraVal = "B";
            $letraCos = "F";
            break;
        case '2': // Design
            $letraCod = "O";
            $letraVal = "C";
            $letraCos = "G";
            break;
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,16,$letraCod,$letraVal,$letraCos);
    }  //  End For j     



    $detalle[] = "Hoja 4 Varilux  (No proceso): ";     
    $detalle[] = "Hoja  5 Varilux Comfort: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(4); // de varilux
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 3; $j++) {
     switch ($j) {
        case '1': // Sin AR
            $letraCod = "L";
            $letraVal = "D";
            $letraCos = "I";
            break;
        case '2': // Trio
            $letraCod = "K";
            $letraVal = "C";
            $letraCos = "H";
            break;
        case '3': // Crizal Forte
            $letraCod = "J";
            $letraVal = "B";
            $letraCos = "G";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,15,$letraCod,$letraVal,$letraCos);
    }  //  End For j     

    $detalle[] = "Hoja 6 Varilux Physio: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(5); // de varilux
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 3; $j++) {
     switch ($j) {
        case '1': // Sin AR
            $letraCod = "L";
            $letraVal = "D";
            $letraCos = "I";
            break;
        case '2': // Trio
            $letraCod = "K";
            $letraVal = "C";
            $letraCos = "H";
            break;
        case '3': // Crizal Forte
            $letraCod = "J";
            $letraVal = "B";
            $letraCos = "G";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,15,$letraCod,$letraVal,$letraCos);
    }  //  End For j     





  if  ( $request->actualiza == "SI" ) {
    DB::commit();
    $detalle[] = " Actualizo BD !!";
  } else {
    DB::rollBack();
    $detalle[] = " Simulación No Actualizo BD !!";
  }


  return view('mensaje', ['titulo' => "Procesado",
                         'detalles' => $detalle ] );

}  // Fin Actualiza Precio


  public function actualiza_precio_proceso_vieja (Request $request)  {

    // Tomo el archivo elegido y actualiza tabla Productos

    DB::beginTransaction();

    $filename = $request->file("nombre_archivo");

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet = $reader->load($filename);

    $detalle  = []; //Informacion de Salida

    $detalle[] = "Usar https://cloudconvert.com/xlsx-to-jpg   para convertir a Imagen";
  
    $detalle[] = "Hoja 1 Stock:";     
    $sheet = $spreadsheet->setActiveSheetIndex(0); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "T";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "U";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
        $this->proceso_columna($detalle ,$sheet,4,50,$letraCod,$letraVal,$letraCos);
    }  //  End For j     


    $detalle[] = "Hoja 4 Bifocales: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(3); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "U";
            $letraVal = "E";
            $letraCos = "H";
            break;
        case '2': // Stratus
            $letraCod = "V";
            $letraVal = "F";
            $letraCos = "I";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,4,22,$letraCod,$letraVal,$letraCos);

    }  //  End For j     

    $detalle[] = "Hoja 5 Multifocales: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(4); // de MULTIFOCALES
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "T";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "U";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,35,$letraCod,$letraVal,$letraCos);

    }  //  End For j     
    
    $detalle[] = "Hoja 6 Ocupacionales: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(5); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 4; $j++) {
     switch ($j) {
        case '1': //crizal
            $letraCod = "T";
            $letraVal = "C";
            $letraCos = "I";
            break;
        case '2': // forte
            $letraCod = "U";
            $letraVal = "D";
            $letraCos = "J";
            break;
        case '3': // Alize Uv 
            $letraCod = "V";
            $letraVal = "E";
            $letraCos = "K";
            break;
        case '4': // sin ar 
            $letraCod = "W";
            $letraVal = "F";
            $letraCos = "L";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,7,38,$letraCod,$letraVal,$letraCos);
    }  //  End For j     
  
    $detalle[] = "Hoja 7 Lentes de Contacto: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(6); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "Q";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "R";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,34,$letraCod,$letraVal,$letraCos,'LC');

    }  //  End For
    
    $detalle[] = "Hoja 8 Laboratorio: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(7); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "T";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "U";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,4,39,$letraCod,$letraVal,$letraCos);

    }  //  End For j     
    
    $detalle[] = "Hoja 9 Laboratorio FOTO: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(8); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "S";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "T";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,4,20,$letraCod,$letraVal,$letraCos);

    }  //  End For j     


    $detalle[] = "Hoja 10 X Serie: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(9); // de varilux
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': // XClusive
            $letraCod = "N";
            $letraVal = "B";
            $letraCos = "F";
            break;
        case '2': // Design
            $letraCod = "O";
            $letraVal = "C";
            $letraCos = "G";
            break;
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,16,$letraCod,$letraVal,$letraCos);
    }  //  End For j     


    $detalle[] = "Hoja 11 Kodak: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(10); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 3; $j++) {
     switch ($j) {
        case '1': // prevencia
            $letraCod = "Q";
            $letraVal = "B";
            $letraCos = "G";
            break;
        case '2': // Sappire
            $letraCod = "R";
            $letraVal = "C";
            $letraCos = "H";
            break;
        case '3': // Forte 
            $letraCod = "S";
            $letraVal = "D";
            $letraCos = "I";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,20,$letraCod,$letraVal,$letraCos);
    }  //  End For j     

    $detalle[] = "Hoja 12 Varilux  (No proceso): ";     
    $detalle[] = "Hoja 13 Varilux Comfort: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(12); // de varilux
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 3; $j++) {
     switch ($j) {
        case '1': // Sin AR
            $letraCod = "L";
            $letraVal = "D";
            $letraCos = "I";
            break;
        case '2': // Trio
            $letraCod = "K";
            $letraVal = "C";
            $letraCos = "H";
            break;
        case '3': // Crizal Forte
            $letraCod = "J";
            $letraVal = "B";
            $letraCos = "G";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,15,$letraCod,$letraVal,$letraCos);
    }  //  End For j     

    $detalle[] = "Hoja 14 Varilux Physio: ";     
    $sheet = $spreadsheet->setActiveSheetIndex(13); // de varilux
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 3; $j++) {
     switch ($j) {
        case '1': // Sin AR
            $letraCod = "L";
            $letraVal = "D";
            $letraCos = "I";
            break;
        case '2': // Trio
            $letraCod = "K";
            $letraVal = "C";
            $letraCos = "H";
            break;
        case '3': // Crizal Forte
            $letraCod = "J";
            $letraVal = "B";
            $letraCos = "G";
            break;       
      } // fin switch 
      $this->proceso_columna($detalle,$sheet,5,15,$letraCod,$letraVal,$letraCos);
    }  //  End For j     

    if  ( $request->actualiza == "SI" ) {
      DB::commit();
      $detalle[] = " Actualizo BD !!";
    } else {
      DB::rollBack();
      $detalle[] = " Simulación No Actualizo BD !!";
    }


    return view('mensaje', ['titulo' => "Procesado",
                           'detalles' => $detalle ] );



  }  // Fin Actualiza Precio

  protected function proceso_columna( &$detalle, $sheet,$filaini,$filafin,$letraCod,$letraVal,$letraCos,$familia ='LEN')
  {
    
    // Recorro las filas que tiene datos
    for ($i = $filaini; $i <= $filafin; $i++) 
    {
        $codigo =  trim($sheet->getCell($letraCod.$i)); //Retorna valor
        if ($codigo <> "") {
            $accion = "";
            $producto = "";
            $precio = $sheet->getCell($letraVal.$i)->getCalculatedValue(); 

          //   if ( $codigo == 'LPFL') {
          //    dd ($precio, $sheet-    >getCell($letraVal.$i),$sheet->getCell($letraVal.$i)->getcalculatedValue() );
          //   } 

            $costo =  $sheet->getCell($letraCos.$i)->getCalculatedValue(); //Retorna valor
            if ( !is_numeric($costo)  ) {
              $detalle[] =  ".." . $codigo . "  Error: Dato Costo " . $costo ;
              $accion = "Error: De dato Costo";  //  +  $precio + " Costo:" + $costo;
              $costo = 0;  
            }  
            // Validar si son correctos los nros
            if ( !is_numeric($precio)  ) {
              $detalle[] =  ".." . $codigo . "  Error: Dato Precio " . $precio ;
              $accion = "Error: De dato Precio";  //  +  $precio + " Costo:" + $costo;
              $precio2 = 0;  
            }else{

              // Temporal para promo Tarjeta 4/2024
              //$precio = $precio * 1.1;  // Mas el 10%

              $precio2 = $precio * 0.9;  // Menos el 10%

              // Actuzalizar Bd , busco el producto
              if  ( ! $oproducto  = Producto::findCodigo($familia,$codigo ) ) {
                //abort(402, 'Error: No se encontro el Id:' . $codigo);
                $detalle[] =  ".." . $codigo . "  Error: No se encontro en tabla Productos";
                return ;  
              };


              $oproducto->Prod_Precio = $precio;
              $oproducto->Prod_Precio2 = $precio2;
              $oproducto->Prod_Costo = $costo;
              $oproducto->Prod_UsuUltMan = 'PrecioExcel';
              $producto  = $oproducto->Prod_Descripcion;

              if ($oproducto->actualizar() ) {
                 if ($oproducto->indicadorModifico ) {
                       $accion =  " ACTUALIZADO ";
                 }        
              }      
            }  // if validacion
            $detalle[] =  ".." . $codigo . "  " . $producto .  "    Precio: " . $precio . " Precio2: " . $precio2 . " Costo:" . $costo . " " . $accion; 

        } // End Codigo
  }  //  End For Filas   
 } // End proceso columna



  public function consultaprecio (Request $request)  {

      return view('productos.consultaprecio');

  } // Fin Consulta Precio


  public function buscaproducto (Request $request)  {

    //  Lo utiliza el auto completar 
    //  Trae 20 productos que concuerden con lo ingresado
    // Estado = T para que traiga tambien los inactivos
    $estado = $request->estado;

    $resbd = Producto::listar($_GET["familia"],$_GET["terms"],20,'' ,'' ,$estado);


    // Armo respuesta en un vector
    $res = [];
    foreach ($resbd as $elem) {
        $res[] = [
            'id' => $elem->prod_id,
            'idweb' => $elem->prod_idweb,
            'name' => $elem->prod_id . ' - ' . $elem->prod_descripcion,
            'descripcion' => $elem->prod_descripcion,
            'categoria' => $elem->prod_categoria,
            'costo' => $elem->prod_costo,
            'precio' => $elem->prod_precio,
            'stock01' => $elem->stock01,
            'stock02' => $elem->stock02,
            'precio2' => $elem->prod_precio2
        ];
    }

    return response()->json($res);

  } // Fin buscaproducto


  public function consultaprecio2 (Request $request)  {

    
    //  Para consultar Cotizacion  
    $aux =  cotizacion::mtoEnPesos("R",100,'',$cotreal);

    //  dd($mtoreales,$valorReal) ;

    if  ( ! $oproducto  = Producto::findCodigo($_GET["familia"],$_GET["codigo"] ) ) {
        $this->retornoerror($_GET["familia"] ."&nbsp;" . $_GET["codigo"] , "Error al buscar producto" ) ;
        exit;
    } 
    $oproducto->Prod_UsuUltMan =  'Sin Conecc';   
    if (Auth::user() )  $oproducto->Prod_UsuUltMan =  Auth::user()->name;   
    $oproducto->insertHistoria( 'WEBConPrecio','','');
    

    echo "<div class='panel panel-success'>";
    echo "<div class='panel-heading'>";
    echo "<h3 class='panel-title'>" . $oproducto->Prod_Familia ."&nbsp;" . $oproducto->Prod_Id  .":&nbsp;" . $oproducto->Prod_Descripcion . "</h3> </div>";
      echo "<div class='panel-body'>";
      
      echo "<table class='table table-striped'>";
      echo "<tr>";
      echo "<td>Precio  $</td>";
      echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio, 2, ",", ".") . "</b></td>";
      echo "<td align='right'><button type='button' onClick='vender(" . $oproducto->Prod_Precio . ",0)' class='btn btn-success'>Vender</button></td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td>Reales</td>"; 
      echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio / $cotreal, 2, ",", ".") . "</b></td>";
      echo "<td></td>";
      echo "</tr>";
      echo "</table>";

      echo "Cotizacion del Real " . number_format( $cotreal , 2, ",", ".") ;
      echo "<br>";
      //echo "Stock Actual: " . number_format($oproducto->prod_stock, 0, ",", ".");
      //echo "<br>";
                $descuento = $oproducto->Prod_Precio - $oproducto->Prod_Precio2;
                if ($oproducto->Prod_Precio > 0 ) {
                    $porc_descuento = $descuento / $oproducto->Prod_Precio * 100;
                }else{

                $porc_descuento = 0;
                }
      echo "<br>";    
      echo "<h4><b>Al Contado "  . number_format($porc_descuento, 0, ",", ".") . "% desc: </b> $ ". number_format($descuento, 2, ",", ".") . " </h4>";
      echo "<table class='table table-striped'>";
      echo "<tr>";
      echo "<td>Precio  $</td>";
            
      echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio2, 2, ",", ".") . "</b></td>";
      echo "<td align='right'><button type='button' onClick='vender(" . $oproducto->Prod_Precio . ",".$descuento . " )'  class='btn btn-success'>Vender</button></td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td>Reales</td>";
      echo "<td align='right'><b>" . number_format($oproducto->Prod_Precio2 / $cotreal, 2, ",", ".") . "</b></td>";
      echo "<td></td>";
      echo "</tr>";
      echo "</table>";
            
      echo "<h4><b>Plan Cuotas con Tarjetas:</b></h4>";
      echo "<table class='table table-striped  table-bordered'>";
      echo "<thead>";
      echo "<tr>";
      echo "<th>Cuotas</th>";
      echo "<th>Valor Cuota</th>";
      echo "<th>Total</th>";
      echo "<th>Coefi</th>";
      echo "</thead>";    
    echo "<tbody>";
    $consulta="select TARCUO_CUOTA ,TARCUO_INTERES from tarjetacuotas WHERE TARCUO_ID = 'VI' ORDER BY TARCUO_CUOTA";
    $datos = DB::select($consulta ); 

    foreach ($datos as $row) {
      echo "<tr>";
      echo "<td align='center'>" . $row->TARCUO_CUOTA . "</td>";
      echo "<td align='center'>" . number_format($oproducto->Prod_Precio * $row->TARCUO_INTERES /  $row->TARCUO_CUOTA , 2, ",", ".") . "</td>";
      echo "<td align='right'>" . number_format($oproducto->Prod_Precio * $row->TARCUO_INTERES, 2, ",", ".") . "</td>";
      echo "<td align='right'>" . $row->TARCUO_INTERES . "</td>";
      echo "</tr>";       
    }
    echo "</tbody>";
      echo "</table>";
      
      echo "</div>"; // del panel-body
    echo "</div>"; // del panel
  


  } // Fin Consulta 


  private function retornoerror($titulo, $msg){
  
    echo "<div class='alert alert-danger'>";
    echo "<div class='panel-heading'>";
    echo "   <h3 class='panel-title'><b>" . $titulo  ."</b></h3> </div>";
    echo "<div class='panel-body'>";      
    echo $msg;
    echo "</div>";
      
      
  }//end funcion retornoerror


  public function consultaprecioventa (Request $request)  {

    // Opcion de Ventas en la consulta de precios
    // ----------------------------
    $ocomprobante = new comprobante();
    $ocomprobante->comp_tipoot = "CA"; // Tipo Caja
    if (Auth::user())  $ocomprobante->comp_responsable=Auth::user()->name;
    $ocomprobante->comp_fecmov= fechahoy();
    // Linea de Detalle , estos casos tienen solo una
    $ocomprobante->linea_detalle[0]['familia'] = $_GET["familia"];
    $ocomprobante->linea_detalle[0]['codigo'] = $_GET['codigo'];
    $ocomprobante->linea_detalle[0]['detalle'] = '';
    $ocomprobante->linea_detalle[0]['cantidad'] = 1;
    $ocomprobante->linea_detalle[0]['precio'] = $_GET['monto'];
    $ocomprobante->linea_detalle[0]['tipoiva'] = 1;

      // Linea de Detalle , estos casos tienen solo una
    if ($_GET['descuento'] > 0 ) {
        $ocomprobante->linea_detalle[1]['familia'] = "VAR";
        if ( $_GET["familia"] == 'REC') {
            $ocomprobante->linea_detalle[1]['codigo'] = '0329';
        }else{
             $ocomprobante->linea_detalle[1]['codigo'] = '0349';   
        }
        $ocomprobante->linea_detalle[1]['detalle'] = 'Descuento';
        $ocomprobante->linea_detalle[1]['cantidad'] = 1;
        $ocomprobante->linea_detalle[1]['precio'] = $_GET['descuento'] * -1;
        $ocomprobante->linea_detalle[1]['tipoiva'] = 1;
    }
    /* PARA PRUEBA  Linea de Detalle
    $ocomprobante->linea_detalle[1]['familia'] = "SOL";
    $ocomprobante->linea_detalle[1]['codigo'] = '1000';
    $ocomprobante->linea_detalle[1]['detalle'] = 'ingreso manul';
    $ocomprobante->linea_detalle[1]['cantidad'] = 2;
    $ocomprobante->linea_detalle[1]['precio'] = 1004.54;
    $ocomprobante->linea_detalle[1]['tipoiva'] = 1;
    */
    
    $ocomprobante->nuevo();
    if ($ocomprobante->ret <> '') {
      displaylog("Error en ConsultaPrecioVenta: Al generar NuevoComprobante");
      echo $ocomprobante->ret ;
      exit;
    }
    
    $respuesta = array("html"=>"Ok");
    echo json_encode($respuesta);

  } // Fin Consultaprecioventa


  public function modificaprecio (Request $request)  {

    return view('productos.modificaprecio');

  } 

  public function modificaprecio2 (Request $request)  {

    if  ( ! $oproducto  = Producto::findCodigo($_GET["familia"],$_GET["codigo"] ) ) {
        $resp = [ "success"   => FALSE, 
                  "error_msg" => "No se encontró el producto con código " . $_GET["codigo"] . " de la familia " . $_GET["familia"]
                  ];
        return response()->json($resp);
    } 

    if ($_GET["action"] == "lee_precio") {
          
        $resp = [
                "success" => TRUE,
                "descripcion" => $oproducto->Prod_Descripcion,
                "precio" => $oproducto->Prod_Precio,
                "precio2" => $oproducto->Prod_Precio2
        ];
  
        return response()->json($resp);

    } // Fin lee precio

    if ($_GET["action"] == "modifica_precio") {
        $oproducto->Prod_Precio = $_GET["monto"];
        $oproducto->Prod_Precio2 = $_GET["monto2"];
        $oproducto->Prod_UsuUltMan = 'PrecioWeb';
        $oproducto->actualizar() ;
        $resp = ["success" => TRUE];
        return response()->json($resp);            
    } 

  } // Fin Modificaprecio2

/*  
  Paso a proceso por consola porque demora mucho
  public function actualiza_stock ()  {

      // Pantalla , Pide archivo Precio de Cristales a procesar
     return view('procesa_archivo' ,  ['titulo' => "Proceso Archivo Productos ",
                                       'mensaje' => "Seleccione archivo de productos Generado en Sucursal",
                                       'accion' =>  "actualiza_stock_proceso",
                                       'tipoArchivo' => "Todos/*.*"] );

  } // Fin
*/

 
} // Fin de la Clase
