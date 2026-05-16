<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Laracasts\Flash\Flash;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\Models\lote;  // Lotes de Compras  - Remitos Inter Sucursales
use App\clases\compra; 
use App\Models\producto;  
use App\Models\sucursal;  
use App\Models\proveedor;  

use App\clases\PDFFactura\PDFRemito ;
use Mail;  // Para el envio de mail

class SucursalesController extends Controller
{

  private   $file_pdf_redirec = '';


  public function lista_pedidos ()  {

    $sucursales = sucursal::where('codigo','<>',0)->orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo'); 
    $sucursales = sucursal::combo(Auth::user()->sucursal , 'N' ); //No Incluye todas
    return view('sucursales.lista_pedidos' , [ 'sucursales' => $sucursales ] );

  } // Fin lista Pedidos  

  public function lista_remitos ()  {

    return view('sucursales.lista_remitos'  );

  } // Fin lista Remitos  

  public function buscar(Request $request)
  {
      // Boton de la vista lista de remitos

      $datos = lote::buscarRemitos($request->tipo_consulta);

      return response()->json( ['results' => $datos] );

  }


  public function carga_remito (Request $request)  {

      // Es llamado desde la vista Lista Remitos
    // Carga pantalla de un Compra (Nueva o Continuar cargando alguna)
    if($request->id_lote == 0 ) {
      // Es un Alta
      $loteCompra = new lote; 
      $loteCompra->Lot_Numlot = 0;
      $loteCompra->Lot_FecMov = date("Y-m-d"); // Asume fecha del dia
      $loteCompra->Lot_Operacion  = 'W'; //Remitos Inter Sucursales 
      $loteCompra->Lot_Estado  = 'C'; 
      $loteCompra->Lot_IdProv  = 0; 
      $loteCompra->Lot_Sucursal  = 0;
      $loteCompra->save();
      
    }else{
      // Ya existe , leer los datos de la misma
      $loteCompra = lote::find($request->id_lote);
    }

    $loteCompra->Lot_FecMov = date("Y-m-d" , strtotime($loteCompra->Lot_FecMov ) );
    $loteCompra->Lot_Proveedor = ''; 
      

    $sucursales = sucursal::where('codigo','<>',0)->orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo'); 

    // Retorna todos los datos de la cabecera 
    return view('sucursales.create' , [ 'lote' => $loteCompra,'sucursales' => $sucursales ] );

  } // Fin carga Remito   
  


  public function envia_email (Request $request)  {

    /**
        Entrada: - Id Remito Inter Sucursal
        Salida:  - Mail con archivo pdf
    **/

    $msgError = "";

    // Mandar Mail
    $asunto = "Documentación Inter Sucursal : " . $request->id;
    $file =  $request->id; // "remito_"  . $request->id .".pdf";
    $file_completo =  storage_path() . "/remitos/" .  $file;

    try {
      Mail::send('prueba',[ 'texto' => "Se le envio mercaderia"] , function ($msj) use ($asunto,$file_completo,$file) {
         $msj->to( env('SUCURSAL_ENVIO_MAIL') );
      //   $msj->cc('rogelio.niella@gmail.com');
         $msj->attach($file_completo, [
                    'as' => $file,
                    'mime' => 'application/pdf',
                     ] )->subject( $asunto );
       });
    } catch (\Exception $e) {

        $msgError  = '<b>Error:</b> No se pudo enviar email a la Sucursal <br>' . $e->getMessage();

        //Flash::error('<b>Error:</b> No se pudo enviar email a la Sucursal <br>' . $e->getMessage() );
       // return view('mensaje', ['titulo' => "ERROR",
        //                'mensaje' => "No se envio Mail",
        //                'pdf' =>  $file_completo  ] );
    }      

    return response()->json([
        'msgError' => $msgError  
    ]);

  } // Fin  envia_email  

// http://localhost/centerweb/public/sucursales/genera_remito?id=4184
//  /sucursales/genera_remito?id=4250

  public function genera_remito (Request $request)  {

  //RECORDAR QUE PARA QUE FUNCIONES  public_html   EN SERVIDOR 

 /*  cAMBIAR 
    App/Providers/AppServiceProvider.php

    public function register()
   {
       //
      $this->app->bind('path.public', function() {
       return base_path().'/public_html';
   });
   }   
 */
                 

    $msgError = '';           

    $lote = lote::find($request->id);
    if ( !$lote ) {
          Flash::error('<b>Error:</b> No se encontro datos del lote ' . $request->id );
          return view('mensaje', ['titulo' => "ERROR",
                           'mensaje' => "Lote no Existe",
                           'pdf' => "" , 'id' => ""  ] );
    }

    //  Recorre todos los itema y genera el movimiento de mercaderia por la salida
    $items = compra::leerItems($request->id);
    if( !$items ) {
          Flash::error('<b>Error:</b> No se encontro Items  en lote ' . $request->id  );
          return view('mensaje', ['titulo' => "ERROR",
                           'mensaje' => "Lote vacio" ,
                           'pdf' => "" , 'id' => ""] );
    }

    $this->genera_pdf($lote,$items);  
     dd('ee');


    // Armo datos por archivo json y pdf
    if($lote->Lot_Operacion == 'C'){
        $suc = Sucursal::find($lote->Lot_Sucursal);
        $proveedor = Proveedor::find($lote->Lot_IdProv);
        $titulo = 'COMPRA    Destino: ' . strtoupper( $suc->descripcion) ;
        $origen = "Proveedor : " . $proveedor->Prov_NomFant;
        $sucursalDestino = strtoupper( $suc->descripcion) ;
        $file =  "compra_" . $request->id .".pdf";
    }else{
        $sucori = Sucursal::find($lote->Lot_Sucursal)  ;
        $sucdes = Sucursal::find($lote->Lot_IdProv);
        $titulo = 'REMITO  Destino :' . strtoupper( $sucdes->descripcion) ;
        $origen = "Sucursal de Origen : " . $sucori->descripcion;
        $sucursalDestino = strtoupper( $sucdes->descripcion) ;
        $file =  "remito_" . $request->id .".pdf";
    }    

    $datos = Array
     (
        "titulo" => $titulo, 
        "idlote" => $request->id, 
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
      $file_completo =  public_path() . "/remitos/" .  $file;

      // Guardamos a PDF
      $pdf->Output( $file_completo ,'F' ); //genera el archivo
    } catch (Exception $e) {
          Flash::error('<b>Error:</b> Falló la Generación del PDF: ' . $e->getMessage()  );
          return view('mensaje', ['titulo' => "ERROR",
                      'mensaje' => "No se genero el Comprobante",
                          'pdf' => "" , 'id' => ""] );

    }

    $file_pdf_redirec =  asset('') . "remitos/" . $file;

 //   dd ($file_pdf_redirec , $file_completo );

//    return redirect($file_pdf_redirec); //Muestra el Pdf generado

    $mensaje = "Se genero Comprobante Nro: " .  $request->id;

    return view('mensaje', ['titulo' => "CONFIRMACIÓN",
                           'mensaje' => $mensaje, 
                           'pdf' => $file_pdf_redirec , 'id' => $file] );

    return response()->json([
        'msgError' => $msgError  
    ]);

  } // Fin genera Remito    
 
} // Fin Controller