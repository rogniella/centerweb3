<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Laracasts\Flash\Flash;

use App\clases\compra; 
use App\Models\lote;  // Lotes de Compras
use App\Models\producto;  
use App\Models\proveedor;
use App\Models\sucursal;  

use App\Models\correo;  
use App\clases\PDFFactura\PDFRemito ;

class ComprasController extends Controller
{

  public function index()
  {
      // Pantalla que Lista las compras pendientes
      return view('compras.index'  ) ;
  }

  public function buscar(Request $request)
  {
      // Carga Pantalla de Compras Pendietes, carga la tabla
      $datos = lote::buscarPendientes($request->tipo_consulta);
      return response()->json( ['results' => $datos] );
  }

  public function create(Request $request)
  {
    // Carga pantalla de una Compra (Nueva o Continuar cargando alguna)
    if($request->id_lote == 0 ) {
      // Es un Alta
      $loteCompra = new lote; 
      $loteCompra->Lot_Numlot = 0;
      $loteCompra->Lot_FecMov = date("Y-m-d"); // Asume fecha del dia
      $loteCompra->Lot_Operacion  = 'C'; //lote tipo Compra 
      $loteCompra->Lot_Estado  = 'C'; // En proceso de Carga
      $loteCompra->Lot_IdProv  = 0; 
      $loteCompra->Lot_Sucursal  = 1; //Por defecto, la puede cambiar por pantalla
      $loteCompra->save();
    }else{
      // Ya existe , leer los datos de la misma
      $loteCompra = lote::find($request->id_lote);
    }

    $loteCompra->Lot_FecMov = date("Y-m-d" , strtotime($loteCompra->Lot_FecMov ) );
    $loteCompra->Lot_Proveedor = ''; 
      
    if ($loteCompra->Lot_IdProv != 0 ) {
        $proveedor = proveedor::find($loteCompra->Lot_IdProv);
        $loteCompra->Lot_Proveedor = $loteCompra->Lot_IdProv . ' - ' .  $proveedor->Prov_RazSocial;
    }

    $sucursales = sucursal::combo(Auth::user()->sucursal);

    return view('compras.create' , [ 'lote' => $loteCompra,'sucursales' => $sucursales ] );
  
  }


  public function ActualizaDatosLote(Request $request)
  {
      // Actualiza los datos de Cabecera de la Compra cada vez que cambia alguno
      if($request->idcompra == 0 ) {
          // Es un Alta, el 1er llamado
          $loteCompra = new lote; 
          $loteCompra->Lot_Operacion = 'C';  // Tipo de Lote Compra
          $loteCompra->Lot_Estado = 'C';    //  En Carga
          $loteCompra->Lot_UsuAlta = Auth::user()->name;          
      }else{  
          $loteCompra = lote::find($request->idcompra);
      }    
      $loteCompra->Lot_IdProv =  $request->idprov;
      $loteCompra->Lot_Sucursal  =  $request->sucursal ;
      $loteCompra->Lot_Observ =  $request->observ;
      $loteCompra->Lot_FecMov = $request->fecmov;
      $loteCompra->Lot_Rendimiento   = numdec($request->rendimiento,0);
      $loteCompra->Lot_Factor  = numdec($request->factor,2);

      $loteCompra->save() ;

      // Retorna el IdCompra por si se genero
      return response()->json( [ 'idcompra' => $loteCompra->Lot_Numlot  ] );
  }

  public function ActualizaTotalesLote(Request $request)
  {
      // Actualiza los Totales de la Compra
      if($request->idcompra == 0 ) {
          return response()->json( ['results' => 'Sin lote'] );
      }    
      $loteCompra = lote::find($request->idcompra);
      $loteCompra->Lot_Cantidad =  $request->cantidad;
      $loteCompra->Lot_Monto =  numdec($request->mtolista,2);

      $loteCompra->save() ;

      return response()->json( ['results' => 'Ok'] );
  }


  public function Finalizar (Request $request)
  {
    
    //  Cierra la Compra y genera los movimientos de Stock en los productos
    //  Validar Datos
    // Carga pantalla de un Compra (Nueva o Continuar cargando alguna)
    if($request->idcompra  == 0 ) {
      Flash::error('<b>Error:</b> Debe Ingresar datos a la Compra');
      return redirect('compras/create');
    }    
    $items = compra::leerItems($request->idcompra);
    if( !$items ) {
        Flash::error('<b>Error:</b> Debe Ingresar Articulos a la Compra');
        return view('compras.create' , [ 'lote' => $loteCompra ] );
    }

    // Actualizar estado del Lote segun corresponda
    $loteCompra = lote::find($request->idcompra);

    if ($loteCompra->Lot_Operacion == 'C') {  //Compra
        $loteCompra->Lot_Estado = 'F'; // Indica que esta finalizado
    }else{ // Remitos
      if ($loteCompra->Lot_Estado == 'E') {  // Esta enviado , solo lo confirmo
         $loteCompra->Lot_Estado = 'F'; // Paso a Estado finalizado
         $loteCompra->save() ;
         return response()->json([ 'msgError' => '', 'pdf' => '' ]);         
      }
      // De proceso de carga  - pasa a Enviado
      $loteCompra->Lot_Estado = 'E'; // Indica que esta Enviado
    } // If Operacion

    $loteCompra->Lot_UsuAlta = Auth::user()->name;
    $loteCompra->save() ;

    //  Recorre todos los itema y genera el movimiento de mercaderia por la compra y actualiza Stock
    foreach ($items as $item) {
        // Actualiza el stock
        $producto  = Producto::findCodigo($item->MLot_Familia,$item->MLot_IdProd);

       if ($loteCompra->Lot_Operacion == 'C') {  //Compra
          $asunto =   'Cierre Compra:' . $request->idcompra;
          $producto->Prod_UsuUltMan  = "CompraWEB";
          // Inserta reg en tabla de Movimientos de Producto  y Actualiza Stock
          $producto->addMovimiento('C',$item->MLot_Cantidad,$item->Prod_Costo,
               $request->idcompra , $loteCompra->Lot_IdProv,'','',$loteCompra->Lot_Sucursal);
        }else{  //Remitos
          $asunto =   'Envío Remito:' . $request->idcompra;
          $producto->Prod_UsuUltMan  = "RemitoWEB";
          // Inserta reg en tabla de Movimientos de Producto  y Actualiza Stock
          // Salida de la Suc
          $producto->addMovimiento('I',$item->MLot_Cantidad,$item->MLot_Precio,
               $request->idcompra , $loteCompra->Lot_IdProv,'','',$loteCompra->Lot_Sucursal);
          // Entrada de la Suc
          $producto->addMovimiento('Y',$item->MLot_Cantidad,$item->MLot_Precio,
               $request->idcompra , $loteCompra->Lot_Sucursal,'','',$loteCompra->Lot_IdProv);
        } // If Operacion 

    } // Fin loop de Items   

    $ret = $this->genera_pdf($loteCompra ,$items); 

    // Mando Mail  (Lo deja en tabla para que despues se envie en proceso automático)
    $correo = new Correo();
    $correo->destino = env('SUCURSAL_ENVIO_MAIL'); 
    $correo->adjunto =  $this->file_completo ;
    $correo->asunto = $asunto; 
    $correo->texto = 'Se ingresaron Articulos en el Stock de la Sucursal';
    $correo->save();
//    \Artisan::queue('email:Manda', ['--file' => $file, '--asunto' => $asunto,'--texto' => $textoCuerpo]);
    return response()->json([
        'msgError' => $ret,
        'pdf' => $this->file_pdf_redirec
    ]);

    //lo hace la vista return redirect()->route('compras.index');
        
  } // Fin de Finalizar 


  public function CargaItems(Request $request)
  {
      // Completar datos de 1 compra
      $datos = compra::leerItems($request->idcompra);
      return response()->json( ['results' => $datos] );
  }

  public function DeleteItem(Request $request)
  {
      // Elimina 1 Item de 1 compra
      compra::deleteItem($request->idcompra,$request->fila);
      return response()->json( ['results' => 'Ok'] );
  }

  public function AddItem(Request $request)
  {
     if ($request->idcompra == 0 ) {
        // Es el primer Item, genero Lote
        $lote = new lote;
        $lote->Lot_Operacion = 'C';  // Tipo de Lote Compra
        $lote->Lot_Estado = 'C';    //  En Carga
        $lote->Lot_FecMov = date("Y-m-d"); // Asume fecha del dia
        $lote->Lot_IdProv = 0;
        $lote->Lot_Sucursal = 0;
        $lote->Lot_Observ = '';
        $lote->Lot_UsuAlta = Auth::user()->name;
        $lote->save();        
        $request->idcompra = $lote->Lot_Numlot;
     }

     if ( $request->ind_alta == 'CRI'  ){
      // Alta del Producto  o Actualizo los precios de ser necesario el producto 
        if (! $oproducto = Producto::findCodigo('CRI', $request->idprod )) {
            // Retorna Error
            $ret= 'Error: No se encontro Cristal:' . $request->idprod;
            return response()->json( [ 'error' => $ret ] );              
        }

      // Inserta 1 Item de la compra
      $valores = [
        'mlot_numlot'  => $request->idcompra 
       ,'mlot_sucursal'  => $request->sucursal 
       ,'mlot_fila'  => $request->fila
       ,'mlot_familia'  => 'CRI'
       ,'mlot_idprod'  => $request->idprod
       ,'mlot_cantidad'  => $request->cantidad
       ,'mlot_precio'  => $oproducto->Prod_Costo
       ,'mlot_descripcion'  => ""
      ];

      compra::addItem($valores);
      // Retorna el IdCompra por si se genero
      return response()->json( [ 'idcompra' => $request->idcompra
                                ,'error' => ''
                                ,'descripcion' => $oproducto->Prod_Descripcion
                               ] );

     }else{   // Ingreso por Articulo  
     
      // Inserta 1 Item de la compra
      $valores = [        
        'mlot_numlot'  => $request->idcompra 
       ,'mlot_sucursal'  => $request->sucursal 
       ,'mlot_fila'  => $request->fila
       ,'mlot_familia'  => $request->familia
       ,'mlot_idprod'  => $request->idprod
       ,'mlot_cantidad'  => $request->cantidad
       ,'mlot_precio'  => $request->precio_lista
       ,'mlot_descripcion'  => ""
      ];

      compra::addItem($valores );

      // Alta del Producto  o Actualizo los precios de ser necesario el producto 
      if (! $oproducto = Producto::findCodigo($valores['mlot_familia'], $valores['mlot_idprod'] )) { 
          $oproducto  = new Producto;
          $oproducto->Prod_Familia  = $request->familia;
          $oproducto->Prod_Id  = $request->idprod;
          $oproducto->Prod_Descripcion  = $request->descripcion;
          $oproducto->Prod_Categoria  = $request->categoria;
          $oproducto->Prod_Costo = numdec($request->costo,2);
          $oproducto->Prod_Precio = numdec($request->precio,2);
          $oproducto->Prod_Precio2 = numdec($request->precio_min,2);
          $oproducto->Prod_UsuUltMan = 'CompraWEB';
          $oproducto->save();  
          if ( $request->ind_alta == 'S' ) {
            Producto::actualizaNvoCodigo($request->familia,$request->idprod);
          }      
      }else{
          $oproducto->Prod_Costo = $request->costo;
          $oproducto->Prod_Precio = $request->precio;
          $oproducto->Prod_Precio2 = $request->precio_min;
          $oproducto->Prod_UsuUltMan = 'CompraWEB';
          $oproducto->Prod_Estado = ''; // Re Activo por las dudas
          $oproducto->actualizar() ;        
      }


     } // Fin si es carga por cristales

      // Retorna el IdCompra por si se genero
      return response()->json( [ 'idcompra' => $request->idcompra  ] );

  }  // Fin AddItem

  public function UpdateItem(Request $request) {

      // Actualiza 1 Item de la compra
      $valores = [
        'mlot_numlot'  => $request->idcompra 
       ,'mlot_fila'  => $request->fila
       ,'mlot_cantidad'  => $request->cantidad
       ,'mlot_precio'  => $request->precio_lista
      ];

      compra::updateItem($valores );

      // Actualizo de ser necesario el producto 
      $oproducto  = Producto::findCodigo($request->familia, $request->idprod ) ; 
      $oproducto->Prod_Descripcion  = $request->descripcion;
      $oproducto->Prod_Categoria  = $request->categoria;
      $oproducto->Prod_Costo = $request->costo;
      $oproducto->Prod_Precio = $request->precio;
      $oproducto->Prod_Precio2 = $request->precio_min;
      $oproducto->Prod_UsuUltMan = 'CompraWEB';
      $oproducto->actualizar() ;        

      return response()->json( ['results' => 'ok' ] );
  }


  private function genera_pdf ($lote,$items)  {

    // Armo datos por archivo json y pdf
    if($lote->Lot_Operacion == 'C'){
        $descri_prov = 'Sin Ingresar';
        $suc = Sucursal::find($lote->Lot_Sucursal);
        if ($lote->Lot_IdProv != 0 ){
          $proveedor = Proveedor::find($lote->Lot_IdProv);
          $descri_prov = $proveedor->Prov_NomFant;          
        }
        $titulo = 'COMPRA    Destino: ' . strtoupper( $suc->descripcion) ;
        $origen = "Proveedor : " . $descri_prov;
        $sucursalDestino = strtoupper( $suc->descripcion) ;
        $file =  "compra_" . $lote->Lot_Numlot .".pdf";
    }else{
        $sucori = Sucursal::find($lote->Lot_Sucursal)  ;
        $sucdes = Sucursal::find($lote->Lot_IdProv);
        $titulo = 'REMITO  Destino :' . strtoupper( $sucdes->descripcion) ;
        $origen = "Sucursal de Origen : " . $sucori->descripcion;
        $sucursalDestino = strtoupper( $sucdes->descripcion) ;
        $file =  "remito_" . $lote->Lot_Numlot .".pdf";
    }    

    $datos = Array
     (
        "titulo" => $titulo, 
        "idlote" => $lote->Lot_Numlot, 
        "fecha" =>   date_format(date_create($lote->Lot_FecMov) ,'Ymd'),  //  "20190303" , 
        "observacion" => $lote->Lot_Observ, 
        "Origen" =>  $origen, 
        "sucursalDestinoDescri" => $sucursalDestino, 
        "cantidad" => $lote->Lot_Cantidad, 
        "monto" => $lote->Lot_Monto,
        "productos" => $items 
    );

 //  dd($datos);
    // Generar Pdf
    $config = array( 
     "TRADE_SOCIAL_REASON" => env('RAZON_SOCIAL'),
     "TRADE_CUIT"=> env('CUIT'),
     "TRADE_ADDRESS" => env('DIRECCION'),
     "TRADE_TAX_CONDITION" => env('CONDICION_IVA'),
     "TRADE_INIT_ACTIVITY" => env('INICIO_ACTIVIDAD') 
    );


    $logo_path =  public_path() . '/imagenes/logo.jpg' ; // "c:/logo.jpg";

    //RAN agregado porque daba error
    error_reporting(E_ALL & ~E_NOTICE);
     ini_set('display_errors', 0);
     ini_set('log_errors', 1);
     ob_end_clean();

    try {
      $pdf = new PDFRemito($datos, $config);
      $pdf->emitirPDF($logo_path);
//      $file_completo =  storage_path() . "/remitos/" .  $file;
      $this->file_completo =  public_path() . "/remitos/" .  $file;

      // Guardamos a PDF
      $pdf->Output( $this->file_completo ,'F' ); //genera el archivo
    } catch (Exception $e) {
        return '<b>Error:</b> Falló la Generación del PDF: ' . $e->getMessage()  ;

    }

    $this->file_pdf_redirec =  asset('') . "remitos/" . $file;

    return '';

  } // Fin genera pfd    

} // Fin de la Clase