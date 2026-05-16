<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)
use App\Models\ot;  // Modelos a utilizar

use App\Models\producto; 
use App\Models\sucursal;  

use Barryvdh\DomPDF\Facade as PDF;

class OtController extends Controller
{
    
  public function index()
  {
      // Lista de Ordenes de Trabajo - Pantalla Principal
      $sucursales = sucursal::combo(Auth::user()->sucursal, 'S');
      return view('ot.index', ['sucursales' => $sucursales ] );
  }

  public function consulta ( )
  {     
      // Consulta de 1 Orden
      return view('ot.consulta');
  }

  public function buscar (Request $request) 
  {
      // Tomo parametros de entrada de Pantall Pricipal para filtrar
      $datos = ot::buscar($request->tipoinforme,$request->sucursal, $request->fecha,$request->fechafin,$request->tipoot,$request->estado);
      foreach ( $datos as $row) {
        $ot   = Ot::find_idWEB( $row->Ot_idWEB);
        $row->Ot_Saldo =  round( $row->Ot_Precio -  $ot->Pagos() ,2);
      }  
      return response()->json([
            "success" => TRUE,
            'results' => $datos
      ]);      
  } // Fin Buscar


  public function show(Request $request)
  {

    // Tiene que estar, lo utiliza para mostrar el index
    // Se utiliza en muchas opciones  llama a la ventana de Modificar y Consultar

    $idWEB = $request->idWEB;
 
    // Si se pidio solo por Nro de Ot, busco que es
    if($request->ot > 0 and $idWEB == 0) {
        $sucursal = 1;
        if ( env('CUIT') == '27233589611'  and $request->ot < 70000 ) {
            $sucursal = 2; //Mercedes
        }      
        if($request->tipo_ot == '') {
          $request->tipo_ot = 'A'; // Si no se paso nada , asumo A
        }  
        $ot = Ot::find_suc_id($sucursal, $request->tipo_ot, $request->ot );
        if ( !$ot ) {
           return "Error: NO se encontro OT Anteojos: " . $request->ot . " Suc:" . $sucursal  ;
        }
        $idWEB = $ot->Ot_idWEB;
    }

    $ot   = Ot::find_idWEB($idWEB);

    if ( !$ot ) {
         return "Error: NO se encontro OT IdWEB " . $idWEB  ;
    }


   $datos = [];

   $datos[] = [ 'titulo'  => '<b>Orden Nro:</b>', 
                'valor' =>  '<b>'. $ot->Ot_Id  . " &nbsp;&nbsp;&nbsp;Sucursal: " . $ot->Ot_Sucursal .  '</b>' ];
   $datos[] = [ 'titulo'  => '<b>Pedida:</b>', 'valor' => date("d-m-Y", strtotime($ot->Ot_FecPedido)) ];
   $datos[] = ['titulo' =>'<b>Prometida:</b>','valor' => date("d-m-Y", strtotime($ot->Ot_FecPrometida)) ];
   $datos[] = [ 'titulo'  => '<b>Cliente:</b>', 
                'valor' =>  $ot->Cliente->Cli_ApeNom . " (" . $ot->Ot_IdCli .")" ];
   $datos[] = [ 'titulo'  => '<b>Teléfono:</b>', 
                'valor' =>   $ot->Cliente->Cli_Pais . " " . $ot->Cliente->Cli_Telefono  ];
   $datos[] = [ 'titulo'  => '<b>Vendedor:</b>', 'valor' =>  $ot->Ot_Vendedor ];

  $datos[] = [ 'titulo'  => '&nbsp;', 'valor' =>   '&nbsp;' ];
  // $datos[] = [ 'titulo'  => '___________', 'valor' =>   '' ];
 
    switch ($ot->Ot_Tipo) {
      case ($ot->Ot_Tipo == 'A' || $ot->Ot_Tipo == 'G') :  // Anteojos Recetados

        $tipoLentes =  substr($ot->DatosREC->OtAnt_Bifocal,0,1) ;
        $datos[] = [ 'titulo'  => '<b>Tipo Lentes:</b>',
                     'valor' => $ot->Descripcion_Tipo_Lentes ( $tipoLentes )];

        $ot->Clase_Lentes = "";
        $adision = "";
        $productoLejos = "";
        $armazonLejos = "";
        if ( $ot->DatosREC->OtAnt_Bifocal !="" ) {
          if ( $armazon  = Producto::findCategoria( "LEN", $ot->DatosREC->OtAnt_Bifocal ) ) {
                $ot->Clase_Lentes = $armazon->Prod_Descripcion ;
            }else{
                $ot->Clase_Lentes = "Error:No encontre Producto:" . $ot->DatosREC->OtAnt_Bifocal;                  
          }  
        if ($ot->DatosREC->OtAnt_LejArmazon != ''){
          if ( $armazon  = Producto::findCodigo( $ot->DatosREC->OtAnt_LejFamArmazon, $ot->DatosREC->OtAnt_LejArmazon ) ) {
                  $ot->ArmazonLejos = $armazon->Prod_Descripcion ;
            }else{
                 $ot->ArmazonLejos = "Error:No encontre Arm Lej en Producto";                  
          }  
        }


          if( $tipoLentes == 'M' or $tipoLentes == 'B') {
             $datos[] = [ 'titulo'  => '<b>Producto:</b>', 'valor' =>  $ot->Clase_Lentes ];
             $datos[] = [ 'titulo'  => '<b>Armazón:</b>', 'valor' =>  $ot->DatosREC->OtAnt_LejArmazon . " - " . $ot->ArmazonLejos ];
             if ($ot->DatosREC->OtAnt_AlturaCen > 0) {
                 $adision = " (ADD +" . number_format($ot->DatosREC->OtAnt_AlturaCen,2)  .")";
             }
          }else{  
             $productoLejos =  $ot->Clase_Lentes;
             $armazonLejos =  $ot->DatosREC->OtAnt_LejArmazon . " - " . $ot->ArmazonLejos;
          }
        }

        if ( $ot->DatosREC->OtAnt_LejMaterial !="" ) {
            $datos[] = [ 'titulo'  => '<b>Lejos:</b>', 'valor' =>  ''];          
            if ( $productoLejos !="" ) {
              $datos[] = [ 'titulo'  => '', 'valor' =>  "<b>Producto:</b>" .  $productoLejos ];
            }
            
            $datos[] = [ 'titulo'  => '', 'valor' => 
              lineaDescriGrado('OD',$ot->DatosREC->OtAnt_LejODEsf,$ot->DatosREC->OtAnt_LejODCil,$ot->DatosREC->OtAnt_LejODGrad ). "<br>" .
              lineaDescriGrado('OI',$ot->DatosREC->OtAnt_LejOIEsf,$ot->DatosREC->OtAnt_LejOICil,$ot->DatosREC->OtAnt_LejOIGrad ) ];
              if ( $armazonLejos !="" ) {
               $datos[] = [ 'titulo'  => '', 'valor' =>  "<b>Armazón:</b>" .  $armazonLejos ];
              }

        }


        if ( $ot->DatosREC->OtAnt_CerMaterial !="" ) {
            $datos[] = [ 'titulo'  => '<b>Cerca: </b>' .  $adision . '', 'valor' =>  ''];          
            $ClaseLenCerca = "";
            if ( $ot->DatosREC->OtAnt_ClaseLenCerca !="" ) {
              if ( $armazon  = Producto::findCodigo ( "LEN", $ot->DatosREC->OtAnt_ClaseLenCerca ) ) {
                $ClaseLenCerca = $armazon->Prod_Descripcion ;
              }else{
                $ClaseLenCerca = "Error:No encontre Producto - " . $ot->DatosREC->OtAnt_ClaseLenCerca;     
              }
              $datos[] = [ 'titulo'  => '', 'valor' => "<b>Producto:</b>" . $ClaseLenCerca];          
            }

            $datos[] = [ 'titulo'  => '', 'valor' =>   lineaDescriGrado('OD',$ot->DatosREC->OtAnt_CerODEsf,$ot->DatosREC->OtAnt_CerODCil,$ot->DatosREC->OtAnt_CerODGrad ). "<br>" .
              lineaDescriGrado('OI',$ot->DatosREC->OtAnt_CerOIEsf,$ot->DatosREC->OtAnt_CerOICil,$ot->DatosREC->OtAnt_CerOIGrad ) . "<br>"] ;
        }
        if ($ot->DatosREC->OtAnt_CerArmazon != ''){
          if ( $armazon  = Producto::findCodigo ( "REC", $ot->DatosREC->OtAnt_CerArmazon ) ) {
                  $ot->ArmazonCerca = $armazon->Prod_Descripcion ;
            }else{
                 $ot->ArmazonCerca = "Error:No encontre Producto";                  
          }  
          $datos[] = [ 'titulo'  => '', 'valor' =>  "<b>Armazón:</b>" .  $ot->DatosREC->OtAnt_CerArmazon . " - " . $ot->ArmazonCerca  ] ;
        }

        break;
      case 'C':  // Celulares
        if ( $ot->DatosCEL->OtCel_Modelo !="" ) {
          if ( $celu  = Producto::findCodigo ( "CEL", $ot->DatosCEL->OtCel_Modelo ) ) {
              $ot->DatosCEL->Modelo = $celu->Prod_Descripcion ;
          }  
        }

        $datos[] = [ 'titulo'  => '<b>Equipo:</b>', 'valor' => $ot->DatosCEL->Modelo ] ;

        break;
    } // End switch TipoOt
         
    $datos[] = [ 'titulo'  => '&nbsp;', 'valor' =>   '&nbsp;' ];
    $datos[] = [ 'titulo'  => '<b>Notas:</b>', 'valor' =>   $ot->Ot_Observacion  ];
    $senia = $ot->Pagos();
    $saldo = $ot->Ot_Precio - $senia;
    $datos[] = [ 'titulo'  => '<b>Total:</b>', 'valor' =>   '$ ' . $ot->Ot_Precio  ];
    $datos[] = [ 'titulo'  => '<b> &nbsp;&nbsp;&nbsp;Seña:</b>', 'valor' =>   '$ ' . $senia  ];
    $datos[] = [ 'titulo'  => '<b> &nbsp;&nbsp;&nbsp;Saldo:</b>', 'valor' =>   '$ ' . $saldo];

    $datos[] = [ 'titulo'  => '<b>Estado:</b>', 'valor' =>  $ot->Descripcion_Estado ( $ot->Ot_Estado ) ];

    $ot->DetallePagos = $ot->DetallePagos();
    $ot->DetalleProductos = $ot->DetalleProductos();
   // dd($ot->DetalleProductos);
    return view('ot.consultaDetalle' , ['ot' => $ot , 'datos' => $datos ]);

    // return $pdf->download('listado.pdf');
        
    return $pdf->stream();

  }

  public function update(Request $request)
  {

  }


  public function create (Request $request)
  {
     
  }

  public function store (Request $request)
  {
     
  }

} // Fin de la Clase
