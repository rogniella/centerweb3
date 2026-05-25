<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Laracasts\Flash\Flash;

use App\clases\comprobante;
use App\Models\caja;
use App\Models\cotizacion;
use App\Models\sucursal;
use App\Models\factura;
use Illuminate\Support\Facades\DB;


class VentasController extends Controller
{


  public function forma_pago()
  {
      // Lista de Ordenes de Trabajo - Pantalla Principal
      $sucursales = sucursal::combo(Auth::user()->sucursal, 'S');
      return view('ventas.forma_pago', ['sucursales' => $sucursales ] );
  }

  public function forma_pago_carga (Request $request) 
  {
      // Tomo parametros de entrada de Pantall Pricipal para filtrar
      $datos = caja::buscar($request->tipoinforme,$request->sucursal, $request->fecha,$request->fechafin,$request->tipoot,$request->estado);
 
      //Recorro para buscar las facturas
      foreach ( $datos as $row) {
           $registro = Factura::findIdComprobante( $row->Caj_TipoOT , $row->Caj_IdOT ,  $row->Caj_SucursalOri);
           if ($registro) {
               $row->factura = $registro->Fac_Comprobante . " " .$registro->Fac_NroPuntoVta . "-" . $registro->Fac_NroFactura;  
           }    
      }  
      return response()->json([
            "success" => TRUE,
            'results' => $datos
      ]);      
  } // Fin Buscar
    
  public function cuotas_tarjeta(Request $request)
  {
      // Se llama por Ajax desde ventas.js para cargar las cuotas segun la tarjeta seleccionada
      $cuotas = DB::table('tarjetacuotas')
          ->where('TarCuo_Id', $request->tarjeta_id)
          ->where('TarCuo_Estado', 'A')
          ->orderBy('TarCuo_Cuota')
          ->get();

      return response()->json($cuotas);
  }

  public function altas()
  {
      // Pantalla para cargar nueva Venta o Presupuesto
      $sucursales = sucursal::combo(Auth::user()->sucursal , 'N' );
      $cotizacionD = cotizacion::where('Cot_Moneda', 'D')->orderBy('id', 'desc')->first();
      $cotizacionR = cotizacion::where('Cot_Moneda', 'R')->orderBy('id', 'desc')->first();

      $tarjetas = DB::table('tarjetas')
          ->where('Tar_Estado', 'A')
          ->orderBy('Tar_Descri')
          ->get(['Tar_Id', 'Tar_Descri']);

      return view('ventas.create', [
          'sucursales' => $sucursales,
          'cotizacionDolar' => $cotizacionD ? $cotizacionD->Cot_Cotizacion : 1,
          'cotizacionReal' => $cotizacionR ? $cotizacionR->Cot_Cotizacion : 1,
          'tarjetas' => $tarjetas,
      ]);
  }


  public function show(Request $request)
  {

    $comp   = Comprobante::find($request->sucursal, $request->tipo, $request->id);
    if ($comp->ret <> "") { 
      return '<b>' . $comp->ret . '</b>' ; // Para mostrar en pantalla Modal
    } 
    //dd ($comp);
    if ( $comp->comp_tipoot == "PR") {
      $html = "<b class='active'>Presupuesto Nro: " .  $comp->comp_id  . " &nbsp;&nbsp;&nbsp;Sucursal: " . $comp->comp_sucursal .  '</b> &nbsp;&nbsp;&nbsp;' ;
    }else{
      $html = "<b class='active'>Venta Nro: " .  $comp->comp_id  . " &nbsp;&nbsp;&nbsp;Sucursal: " . $comp->comp_sucursal .  '</b> &nbsp;&nbsp;&nbsp;' ;
    }
    $html .= '<b>Vendedor:&nbsp;&nbsp;</b>'. $comp->comp_responsable  ."<br>" ;
    $html .= "<br>";
    $html .= '<b>Fecha:&nbsp;&nbsp;</b>'. date("d-m-Y", $comp->comp_fecmo) ."<br>" ;
    $html .= '<b>Comprobante:&nbsp;&nbsp;</b>'.  $comp->tipo_de_comprobante  . "&nbsp;" . $comp->punto_de_venta . "-" . $comp->numero_de_factura  ."<br>" ;
    $html .= '<b>Cliente:&nbsp;&nbsp;</b>'. $comp->cliente->Cli_ApeNom . " (" . $comp->comp_idcli .")<br>" ;

    // Recorro todas las lineas de Detalle
    $html .= "<br>";
    $html .= "<table class='table table-bordered'>";
    $html .= "<tr class='active'>";
    $html .= "<th>Producto </th>";
    $html .= "<th class='text-center'>Cantidad</th>";
    $html .= "<th class='text-center'>Precio Unit.</th>";
    $html .= "<th class='text-center'>Bonif</th>";
    $html .= "<th class='text-center'>Importe</th>";
    $html .= "</tr>";

    
    foreach ($comp->linea_detalle as  $linea) {     
      $html .= "<tr>";
      $html .= "<td>" .  $linea['detalle'] . "</td>";
      $html .= "<td class='text-center'>" .  $linea['cantidad'] . "</td>";
      $html .= "<td class='text-right'>" .  $linea['precio_unitario'] . "</td>";
      $html .= "<td class='text-right'>" .  $linea['importe_bonif'] . "</td>";
      $total = $linea['precio_unitario_tomado'] *  $linea['cantidad'];
      $html .= "<td class='text-right'>" .  $total . "</td>";
      $html .= "</tr>";
    } // Fin for lineas detalle
    $html .= "</table>";
    if ( $comp->comp_tipoot != "PR") {
       // Recorro todas las Formas de Pago
       $html .= "<b>Formas de Pago:</b><br>";
       $html .= "<table class='table table-bordered'>";
       $html .= "<tr>";
       $html .= "<th class='text-center'>Moneda</th>";
       $html .= "<th class='text-center'>Monto</th>";
       $html .= "<th class='text-left'>Detalle</th>";
       $html .= "</tr>";
       foreach ($comp->linea_pago as  $linea) {     
        $html .= "<tr>";
        $html .= "<td class='text-center'>" .  $linea['formapago'] . "</td>";
        $html .= "<td class='text-right'>" .  $linea['monto'] . "</td>";
        $html .= "<td class='text-left'>" .  $linea['detalle'] . "</td>";
        $html .= "</tr>";
       } // Fin for Formas de Pago
       $html .= "</table>";
    }
    $total =  $comp->importe_gravado +  $comp->importe_iva ;
    $html .= '<div class="btn-warning"> <b>TOTAL:&nbsp;&nbsp; $</b>'.$total  ."</div><br>" ;

    if ( $comp->comp_observaciones != "") {
      $html .= "<br><b>Observaciones:</b>" . $comp->comp_observaciones . "<br>" ;
    }  

    /* No queda bien aqui
     if ( $comp->comp_tipoot == "PR") {
      $html .= '<button type="button" class="btn   btn-danger   btn-xs"' .  'title="Imprimir PDF" onclick="imprimePDF(' . $comp->comp_sucursal  . ',' . $comp->comp_tipoot   . ',' .  $comp->comp_id . ','  . "Estado"  .')">' .  '<i class="fa fa-file-pdf-o"></i>  </button> &nbsp;';
     }  
    */


    return $html; // Para mostrar en pantalla Modal

  }


  public function generaComprobanteAFIP(Request $request)
  {

    // Es llamado desde Vb , o para reintentar si quedo con error 
    // Genera Factura en AFIP
    // Completo datos en tablas:     
    //   -  Actualiza en tabla Facturas

    $dirPDF = "";

    $comprobante   = Comprobante::find($request->sucursal, $request->tipo, $request->id);

    if( $comprobante->ret == "") {
      
      if($comprobante ->auxEstadoFactua != "K") {
        $comprobante->ret = "El Comprobante No esta en Estado pendiente AFIP";
      }else{
        $comprobante->ret = $comprobante->generaComprobanteAFIP();  
        if( $comprobante->ret == "") {
          $filePDF = $comprobante->GeneraPDF(); //Re Impresion
          $dirPDF =  asset('') .  $filePDF ;
          if($request->soloGenera != "si")  return redirect($dirPDF); //Muestra el Pdf generado
        }  
      }
    }

    if($request->ajax() ) {
      return response()->json([
        'pdf' =>   $dirPDF,
        'retError' => $comprobante->ret
      ]);
    }else{
      Flash::error("ERROR:" . $comprobante->ret );
      return view('Facturas.error', ['comprobante' => $comprobante ] )  ;
    }  
  }


  public function imprimePDF(Request $request)
  {

// http://localhost/gestion/public/ventas/imprimePDF?tipo=FC&id=26240
// http://127.0.0.1:8000/ventas/imprimePDF?sucursal=1tipo=PR&id=60333

    $dirPDF = "";

    $comprobante   = Comprobante::find($request->sucursal, $request->tipo, $request->id);

    if( $comprobante->ret == "") {
        // dd($comprobante);
        if( $comprobante->comp_tipoot == "PR") {
          $filePDF = $comprobante->GeneraPRESUPUESTO(true); //Re Impresion
        }else {
          $filePDF = $comprobante->GeneraPDF(true); //Re Impresion
        }
        $dirPDF =  asset('') .  $filePDF ;
        if($request->soloGenera != "si")  return redirect($dirPDF); //Muestra el Pdf generado
    }

    if($request->ajax() ) {
      return response()->json([
        'pdf' =>   $dirPDF,
        'retError' => $comprobante->ret
      ]);
    }else{
      return  "ERROR:" . $comprobante->ret;
    } 

  }

  public function store (Request $request)
  {
     
    // Inserta un nuevo Comprobante en todas las Tablas Correspondientes
    // Es llamado por la vista ventas/create

    $ocomprobante = new comprobante();

    $ocomprobante->comp_sucursal = $request->sucursal; //Toma la del usuario Conectado
    if ( $request->operacion == "Vta") {
        $ocomprobante->comp_tipoot = "VT"; // Venta Web
        $ocomprobante->punto_de_venta = env('PUNTO_DE_VENTA'); // Cuidado depende de la sucursal
    }else{
        $ocomprobante->comp_tipoot = "PR"; // Presupuesto
        $ocomprobante->punto_de_venta = $ocomprobante->comp_sucursal;
    }        
    $ocomprobante->tipo_de_factura=$request->id_tipo_cbte; // Tipo Factura Numerico segun AFIP 
    $ocomprobante->fecha_factura=$request->fecha; // Ingresada por pantalla, solo fecha
    $ocomprobante->comp_fecmov=fechahorahoy(); // Fecha y hora actual
    $ocomprobante->comp_idcli =$request->id_cliente;
    $ocomprobante->comp_responsable=$request->id_vendedor;

    $ocomprobante->punto_facturaOriginal = $request->punto_facturaOriginal; //Para las anulaciones   
    $ocomprobante->facturaOriginal = $request->numeroOrig; //Para las anulaciones   
    $ocomprobante->comp_observaciones = $request->observaciones;    

    $items_venta = json_decode($request->json_items, TRUE);

    foreach ($items_venta as $item) {
        $importe_bonif = 0;
        $precio_unitario_tomado = $item['precio_unitario'];
        if($item['bonif_unitario'] > 0 ) {
           $importe_bonif = round( $item['precio_unitario'] * ( 1 - ( $item['bonif_unitario'] /100)),2) ;
           $precio_unitario_tomado =  $importe_bonif;
        }
      // se va agregando nuevos
        $ocomprobante->linea_detalle[] = [
          'familia' => $item['id_familia'],
          'codigo' => $item['id_producto'],
          'detalle' => $item['descrip_producto'],
          'cantidad' => $item['cantidad'],
          'bonif' => $item['bonif_unitario'],
          'precio_unitario' => $item['precio_unitario'],
          'precio_unitario_tomado' => $precio_unitario_tomado,
          'tipoiva' => $item['id_iva'],
          'importe_bonif' =>  $importe_bonif
        ];
        //print_r($item );                
    }

    $items_pagos = json_decode($request->json_pagos, TRUE);
    foreach ($items_pagos as $item) {
        // se va agregando nuevas Formas de Pago
        $ocomprobante->linea_pago[] = [
          'detalle' => $item['detalle'],
          'moneda' => $item['moneda'],
          'tarjeta' => $item['tarjeta_id'],
          'cuotas' => $item['cuotas'],
          'monto' => $item['monto'],
          'montomonori' => $item['montomonori'],
          'cotizacion' => $item['cotizacion']
        ];
    }

    $dirPDF = "";

  //  dd( $ocomprobante );
    $ocomprobante->nuevo();
    if($ocomprobante->auxErrorAfip != "") {
             $ocomprobante->ret = "";
             $mensaje =  "<br> ***Comprobante Generado con ERROR AL GENERA EN AFIP*** <br>" .
             $ocomprobante->auxErrorAfip . "<br> Reintenete mas tarde ";
        return response()->json([
        'mensaje' =>  $mensaje    ,
        'pdf' =>   $dirPDF    ,
        'id' => $ocomprobante->comp_id,
        'errorAFIP' =>   $ocomprobante->auxErrorAfip ,
        'retError' => $ocomprobante->ret
    ]);

    } 
   // dd($ocomprobante->auxErrorAfip , $ocomprobante->ret );


    // Una vez grabado , lo leo para imprimir
    $ocomprobante   = Comprobante::find($ocomprobante->comp_sucursal, $ocomprobante->comp_tipoot, $ocomprobante->comp_id );
  //  dd($ocomprobante );
    // Redirecciona a la ultima pagina llamada:
    if($ocomprobante->CAE == "") {
      if ($ocomprobante->comp_tipoot == "PR" ) {  // Presupuesto
        $filePDF = $ocomprobante->GeneraPRESUPUESTO();
        $dirPDF =  asset('') .  $filePDF ;
        $mensaje = "Se ha registrado PRESUPUESTO de manera exitosa ! Id:" . $ocomprobante->comp_id  ;
        $ocomprobante->ret = "";
      }else{   
        $mensaje = "Se ha registrado Venta de manera exitosa ! Id:" . $ocomprobante->comp_id  ;
         if($ocomprobante->auxErrorAfip != "") {
             $ocomprobante->ret = "";
             $mensaje = $mensaje . "<br> ***ERROR AL GENERA EN AFIP*** <br>" .
             $ocomprobante->auxErrorAfip . "<br> Reintenete mas tarde ";
         } 
      }   
    }else{
        
        $filePDF = $ocomprobante->GeneraPDF();
        $dirPDF =  asset('') .  $filePDF ;

        $mensaje ="Se ha registrado Venta Factura:" . $ocomprobante->tipo_de_comprobante . " " .
         $ocomprobante->punto_de_venta .  "-" . $ocomprobante->numero_de_factura . " CAE:" . $ocomprobante->CAE ;
    }

    return response()->json([
        'mensaje' =>  $mensaje    ,
        'pdf' =>   $dirPDF    ,
        'id' => $ocomprobante->comp_id,
        'errorAFIP' =>   $ocomprobante->auxErrorAfip ,
        'retError' => $ocomprobante->ret
    ]);
        
  } // Fin de Store  

} // Fin de la Clase