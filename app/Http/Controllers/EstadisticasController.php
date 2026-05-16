<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)
use App\Models\cotizacion;  
use App\Models\sucursal;  
use App\Models\familia;  
use App\Models\minforme;  
use App\Models\minformecod;  
use Carbon\Carbon; 

class EstadisticasController extends Controller
{

    private $data_coti = [];

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function consolidado()
    {     
        return view('estadisticas.consolidado' );
    } 


// http://localhost/centerweb2/public/estadisticas/consolidado_proceso?fecha=2023-3-1&fechafin=2023-3-31&sucursal=1

    private function carga_cotiza_dolar( )
    {     

        // Bluelytics proporciona una API pública que permite acceder al histórico
        //     del dólar blue en formato JSON
        
        $url = "https://api.bluelytics.com.ar/v2/evolution.json";
        
        // Inicializar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Ejecutar la solicitud
        $response = curl_exec($ch);
        
        // Manejar errores
        if (curl_errno($ch)) {
            return "Error de cURL: " . curl_error($ch);
        }
        curl_close($ch);
        // Decodificar JSON
        $this->data_coti = json_decode($response, true);
      //  dd($data);        
        return "";
        
    }

    private function buscar_cotiza_dolar( $fecha_buscada )
    {

        // Fecha a buscar (formato: YYYY-MM-DD)
        
        $max_retrocesos = 20;  // Cuántos días hacia atrás como máximo querés buscar
        $encontrado = false;
        $intentos = 0;
        
        while (!$encontrado && $intentos < $max_retrocesos) {        
          foreach ($this->data_coti as $item) {
            if ($item['date'] === $fecha_buscada and $item['source'] == "Blue" ) {
             //   echo "Fecha encontrada: " . $item['date'] . "<br>";
             //   echo "Compra: " . $item['value_buy'] . "<br>";
             //   echo "Venta: " . $item['value_sell'] . "<br>";
                $encontrado = true;
                return $item['value_sell']; // Retorno el valor de Venta
                break;
            }
          }      
          if (!$encontrado) {
                // Restar un día
                // Crear un objeto desde una fecha
                displaylog (" NO encontro Cotiza: " .  $fecha_buscada );
                $fecha = Carbon::createFromFormat('Y-m-d', $fecha_buscada);
                $fecha->subDay(); // Restar un día
                // Obtener como string (formato YYYY-MM-DD)
                $fecha_buscada = $fecha->format('Y-m-d');
                $intentos++;
          }
        } // Fin del while
        
        if (!$encontrado) {
            dd("No se encontró Cotización para la fecha: $fecha_buscada");
            return 1;  // "No se encontró la fecha: $fecha_buscada";
        }                        
    }

    public function consolidado_proceso()
    {     

        $moneda = $_GET['moneda'];
      //  $moneda = 2; 
        if (  $moneda == 2) {
            $ret = $this->carga_cotiza_dolar( );
            if ( $ret != ""){
                dd("Error; Al cargar cotizaciones: " . $ret );
            };    
        }


        // Tomo parametros de entrada para filtrar
        $fecha=$_GET['fecha'] . " 00:00:00" ;
        $fechafin=$_GET['fechafin'] . " 23:59:59";
        $sucursal=$_GET['sucursal'] ;
 
        //Columnas del Informe
        $mes1 = array(0,0,0,0,0,0,0,0,0,0,0,0); //Ventas
        $mes2 = array(0,0,0,0,0,0,0,0,0,0,0,0); // Gastos
        $mes3 = array(0,0,0,0,0,0,0,0,0,0,0,0); // Saldo
        $mes4 = array(0,0,0,0,0,0,0,0,0,0,0,0); //Ventas con Tarjetas
        $mes5 = array(0,0,0,0,0,0,0,0,0,0,0,0); //Facturacion
        $mes6 = array(0,0,0,0,0,0,0,0,0,0,0,0); //Rendimiento
        $mes7 = array(0,0,0,0,0,0,0,0,0,0,0,0); //Gastos
        $mes8 = array(0,0,0,0,0,0,0,0,0,0,0,0); //Saldo

        //Ventas y Gastos  (En el select no tomo trasferencias 0900)  
        
        // Rendimiento total
        if ( env('CUIT') == '27233589611'  ) {
             $tipo_informe = 9;
        }else{
             $tipo_informe = 6; // Monte Caseros 
        }    
        $codigos_rend = [];     
        $codigos_sinuso = [];
        $sinuso = '';     
        $ret = $this->codigos_informe($tipo_informe,$codigos_rend,$codigos_sinuso,$sinuso,'');
        // Gastos Totales
        $tipo_informe = 5; 
        $codigos_gto = [];     
        $ret = $this->codigos_informe($tipo_informe,$codigos_gto,$codigos_sinuso,$sinuso,'');

        $filtro = " where Mcaj_fecMov >= '". $fecha . "' and Mcaj_fecMov <= '". $fechafin . "'";
        if ($sucursal != 0 ) {
          $filtro = $filtro . " and mcaj_sucursal = " . $sucursal;
        }
        $consulta= "SELECT  MCaj_Codigo,MCaj_Moneda,MCaj_Monto,Mcod_HyD,mcaj_fecmov, Month(Mcaj_fecMov) as mesbd FROM mcaja join mcodigo on  MCaj_Codigo = MCod_Codigo" . $filtro . " and MCaj_Codigo<>'0900'";
        $datos = DB::select($consulta ); 
        foreach ( $datos as $row) {
            // Si la moneda no es pesos , busca cotizacion
            $mtoPesos = cotizacion::mtoEnPesos( $row->MCaj_Moneda, $row->MCaj_Monto, $row->mcaj_fecmov);

            if (  $moneda == 2) {
                $cotiza = $this->buscar_cotiza_dolar( $row->mcaj_fecmov );
                $mtoPesos = $mtoPesos / $cotiza;
            }        
    
            if ( $row->Mcod_HyD == 'H') {
                //Venta
                $mes1[ $row->mesbd -1] +=   $mtoPesos;
            }else{
                //Gasto 
                $mes2[ $row->mesbd -1] +=   $mtoPesos;
            }    
            //Rendimiento total 
            if (array_key_exists($row->MCaj_Codigo, $codigos_rend)) {
                $rinde =  $codigos_rend[$row->MCaj_Codigo];
                $mes6[ $row->mesbd -1] +=   ($mtoPesos * $rinde) ;
            }    
            //Gastos total 
            if (array_key_exists($row->MCaj_Codigo, $codigos_gto)) {
                $mes7[ $row->mesbd -1] +=   $mtoPesos  ;
            }    
        }    

        //Ventas con tarjeta   
        $filter = " Where Caj_FecMov >= '" . $fecha . "' and Caj_FecMov <= '". $fechafin . "' AND Caj_Moneda = 'T' ";
        if ($sucursal != 0 ) {
            $filter = $filter . "  and caj_sucursalori = " . $sucursal;
        }
        $consulta = "SELECT Caj_Monto , Month(Caj_FecMov) as mesbd , Date(Caj_FecMov) as fecha FROM  caja " . $filter; 
        $datos = DB::select($consulta);
        foreach ( $datos as $row) {


            $mto = $row->Caj_Monto;
            if (  $moneda == 2) {
                $cotiza = $this->buscar_cotiza_dolar( $row->fecha );
                $mto = $mto / $cotiza;
            }        

            $mes4[ $row->mesbd -1] +=   $mto;            
        }    

        //Facturacion  
        $filter = " Where Fac_Fecha >= '" . $fecha . "' and Fac_Fecha <= '". $fechafin . "' ";
        if ($sucursal != 0 ) {
            $filter = $filter . " and Fac_Sucursal = " . $sucursal;
        }
        $consulta = "SELECT Fac_Comprobante, Fac_Total, Fac_Estado , Month(Fac_Fecha) as mesbd ,Date(Fac_Fecha) as fecha FROM  facturas " . $filter; 
        $datos = DB::select($consulta);
        foreach ( $datos as $row) {
          if ( in_array($row->Fac_Estado ,[ 'E', 'C'], true ) ) {
            $mto =  $row->Fac_Total;
            if (  $moneda == 2) {
                $cotiza = $this->buscar_cotiza_dolar( $row->fecha );
                $mto = $mto / $cotiza;
            }        

            // Solo tomo si estan emitidas o contabilizadas    
            if ( in_array($row->Fac_Comprobante ,[ 'S', 'R'], true ) ) {
                // Notas de creditos, restan
                $mes5[ $row->mesbd -1] -=   $mto;            
            }else{
                $mes5[ $row->mesbd -1] +=   $mto;            
            }
          } // if Estado  
        }    

        // Armo respuesta en un vectores para Tabla y Grafico
        $datosgrafico = [];
        $datosgrafico2 = [];
        $datostabla = [];
        $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
        $total1 = 0;
        $total2 = 0;
        $total3 = 0;
        $total4 = 0;
        $total5 = 0;
        $total6 = 0;
        $total7 = 0;
        $total8 = 0;
    
        // Recorro por mes
        for($i = 1; $i < 13; $i++){
            // Saldo = Ventas - Gastos
            $mes3[$i - 1] = ($mes1[$i - 1] - $mes2[$i - 1] ) ;
            // Saldo = Rendimiento - Gastos
            $mes8[$i - 1] = ($mes6[$i - 1] - $mes7[$i - 1] ) ;
            $datosgrafico[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mes4[$i - 1],env('DEC_MONTO'),"",""),  //Tarjeta
             'b'  => number_format($mes5[$i - 1],env('DEC_MONTO'),"","") ); //FActuracion
            $datosgrafico2[] = array(
                'y'  => $mes[$i - 1],
                'a'  => number_format($mes6[$i - 1],env('DEC_MONTO'),"",""),  //Rendimiento
                'b'  => number_format($mes7[$i - 1],env('DEC_MONTO'),"",""), //Gastos
                'c'  => number_format($mes8[$i - 1],env('DEC_MONTO'),"","") ); //Saldo
            $datostabla[] = array(
             'mes'  => $i,
             'periodo'  => $mes[$i - 1],
             'valor1'  => number_format($mes1[$i - 1],env('DEC_MONTO'),",","."),
             'valor2'  => number_format($mes2[$i - 1],env('DEC_MONTO'),",","."),
             'valor3'  => number_format($mes3[$i - 1],env('DEC_MONTO'),",","."),
             'valor4'  => number_format($mes4[$i - 1],env('DEC_MONTO'),",","."),
             'valor5'  => number_format($mes5[$i - 1],env('DEC_MONTO'),",","."),
             'valor6'  => number_format($mes6[$i - 1],env('DEC_MONTO'),",","."),
             'valor7'  => number_format($mes7[$i - 1],env('DEC_MONTO'),",","."),
             'valor8'  => number_format($mes8[$i - 1],env('DEC_MONTO'),",",".") 
            );
            $total1 += $mes1[$i - 1];
            $total2 += $mes2[$i - 1];
            $total3 += $mes3[$i - 1];
            $total4 += $mes4[$i - 1];
            $total5 += $mes5[$i - 1];
            $total6 += $mes6[$i - 1];
            $total7 += $mes7[$i - 1];
            $total8 += $mes8[$i - 1];
        }  // Fin For Mes

        // Totales
        $datostabla[] = array(
         'mes'  => 13,
         'periodo'  => '<b>T O T A L</b>',
         'valor1'  => number_format($total1,env('DEC_MONTO'),",","."),
         'valor2'  => number_format($total2,env('DEC_MONTO'),",","."),
         'valor3'  => number_format($total3,env('DEC_MONTO'),",","."),
         'valor4'  => number_format($total4,env('DEC_MONTO'),",","."),
         'valor5'  => number_format($total5,env('DEC_MONTO'),",","."),
         'valor6'  => number_format($total6,env('DEC_MONTO'),",","."),
         'valor7'  => number_format($total7,env('DEC_MONTO'),",","."),
         'valor8'  => number_format($total8,env('DEC_MONTO'),",",".")
        );

        // Enviar la respuesta Ok.
        $res = [
            "success" => TRUE,
            "grafico" => $datosgrafico,
            "grafico2" => $datosgrafico2,
            "tabla" => $datostabla
        ];

        return response()->json($res);

    } // Fin consolidado_proceso


    public function infrubro_detalle()
    {     

        $sucursales = sucursal::combo(Auth::user()->sucursal , 'S' ); //Incluye todas

        return view('estadisticas.infrubro_detalle', [ 'sucursales' => $sucursales ] );
    } 

/* PASO A CONTROL PRODUCTO
    public function infrubro_detalle_proceso()
    {     

// Tomo parametros de entrada para filtrar
  $sucursal=$_GET['sucursal'];
  $tipo_informe=$_GET['tipo_inf'];
  $operacion = $_GET['operacion'];
  $id_producto = $_GET["id_producto"];
  $desc_producto = $_GET["desc_producto"];  
  $fecha=$_GET['fecha'];
  $fechafin=$_GET['fechafin'];


// detallado

// SELECT * FROM MoviProductos  where mov_fecMov >= #2018-10-01# and   MOV_fecMov < #2018-12-19# and MOV_Familia ='CRI'  and MOV_operacion ='V'  ORDER BY Mov_fecmov desc

//agrupado
//SELECT Mov_Familia,Mov_IdProd, Sum(Mov_Cantidad) , Sum(Mov_Precio) From moviProductos  where mov_fecMov >= #2018-10-01# and   MOV_fecMov < #2018-12-19# and MOV_Familia ='CRI'  and MOV_operacion ='V'  and (Mov_operacion<>'M' and Mov_operacion<>'D')  GROUP BY Mov_Familia,Mov_IdProd


   // Como la fecha tiene hora  hacer:  and  DateValue ( Mov_fecMov) <= DateValue ('". $fechafin . "')

//    $filtro = " where Mov_fecMov >= DateValue ('". $fecha . "') and  DateValue ( Mov_fecMov) <= DateValue ('". $fechafin . "') and MOV_Familia ='". $tipo_informe . "'  and ( MOV_operacion ='" . $operacion . "' or Mov_operacion = 'R' ) and mov_idprod <> '0' ORDER BY Mov_fecmov desc";

    $filtro = " where Mov_fecMov >= '". $fecha . "' and Mov_fecMov <= '". $fechafin . "' and MOV_Familia ='". $tipo_informe . "'  and ( MOV_operacion ='" . $operacion . "' or Mov_operacion = 'R' ) and mov_idprod <> '0'";


    if ($sucursal != 0 ) {
      $filtro = $filtro . " and mov_sucursal = " . $sucursal;
    }
    if ($id_producto != '' ) {
      $filtro = $filtro . " and mov_idprod = '" . $id_producto . "'";
    }
    if ($desc_producto != '' ) {
      $filtro = $filtro . " and prod_descripcion LIKE '%" . $desc_producto . "%'";
    }
    
    $filtro = $filtro . " ORDER BY Mov_fecmov desc";

   $consulta= "SELECT mov_sucursal, mov_idprod,prod_descripcion,mov_fecmov,mov_cantidad,mov_precio,mov_operacion,mov_motivo FROM productos INNER JOIN moviproductos ON (productos.Prod_Id = moviproductos.Mov_IdProd) AND (productos.Prod_Familia = moviproductos.Mov_Familia) " . $filtro;
    

    $resbd = DB::select($consulta ); 

    $datostabla = [];
        
    
     foreach ($resbd as $objelem) {
        $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia

            $cantidad = $elem["mov_cantidad"];                

            if($operacion == 'V') { // Ventas
                $cantidad = $elem["mov_cantidad"] * -1;                
            }    

            if($operacion == 'C') { // Compras
                if($elem["mov_operacion"] != 'C') { // Compras no tommamos las anulaciones
                    $cantidad = 0; 
                }                   
                $elem["mov_precio"] = $elem["mov_precio"] * -1 ; // lo paso a  positivo 
            }    

            if($cantidad != 0) { // Compras
             $datostabla[] = array(
              'mov_sucursal'  => $elem["mov_sucursal"], 
              'prod_descripcion'  => $elem["mov_idprod"] ."-".$elem["prod_descripcion"]  ,
              'mov_precio'  => number_format($elem["mov_precio"],0,"","."), 
              'mov_cantidad'  => number_format($cantidad,0,"","."), 
              'mov_fecmov'  => $elem["mov_fecmov"], 
              'mov_operacion'  => $elem["mov_operacion"], 
              'mov_motivo'  => $elem["mov_motivo"] 
             );    
            }                
    }

  // Enviar la respuesta Ok.
  $res = [
            "success" => TRUE,
            "results" => $datostabla
  ];

    return response()->json($res);

} // Fin infrubro_detalle_proceso
*/


public function infmov_detalle()
{     

      $sucursalesModal = sucursal::combo(Auth::user()->sucursal, 'N'); //No permite todas
      return view('estadisticas.infmov_detalle', ['sucursalesModal' => $sucursalesModal ]);

} // Fin infmov_detalle


public function infmov_detalle_proceso()
    {     

    // Tomo parametros de entrada para filtrar
    $sucursal = $_GET["sucursal"];
    $tipo_informe=$_GET['tipo_inf'];
    $param_codigos = $_GET["codigos"];
    $fecha=$_GET['fecha'] . " 00:00:00" ;
    $fechafin=$_GET['fechafin'] . " 23:59:59";


    // Carga los vectores según el tipo de informe
    // $codigos = [];     y    $rendimento = [];
    $codigos = [];     
    $codigos2 = [];       
    $filtro2 = '';
    $ret = $this->codigos_informe($tipo_informe,$codigos,$codigos2,$filtro2,$param_codigos);


    $filtro = " where Mcaj_fecMov >= '". $fecha . "' and Mcaj_fecMov <= '". $fechafin . "'";
    if ($sucursal != 0 ) {
      $filtro = $filtro . " and mcaj_sucursal = " . $sucursal;
    }


    $consulta= "SELECT MCaj_sucursal, MCaj_idWEB, MCaj_Codigo,MCOD_Descripcion,MCaj_Moneda,MCaj_Monto,Mcod_HyD,mcaj_fecmov,DATE_FORMAT(MCaj_fecMov, '%d/%m/%Y') as f ,mdes_descripcion , mdes_tipoOT,mdes_idfac FROM mcaja join mcodigo on  MCaj_Codigo = MCod_Codigo" . $filtro;
    
    $resbd = DB::select($consulta ); 
 
    $datostabla = [];
        
    
     foreach ($resbd as $objelem) {
        $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
        if (array_key_exists($elem["MCaj_Codigo"], $codigos)) { // Si es uno de los codigos Seleccionado
            $rinde =  $codigos[$elem["MCaj_Codigo"]];
            $txtRinde = '';
            if ($rinde < 1) {
                $txtRinde = '   (Rendim:' . number_format($rinde * 100,0,"",".") . '%)';
            }
            // Si la moneda no es pesos , buscar cotizacion
            $mtoPesos = cotizacion::mtoEnPesos( $elem["MCaj_Moneda"], $elem["MCaj_Monto"], $elem["mcaj_fecmov"]) * $rinde;
            $datostabla[] = array(
             'sucursal'  => $elem["MCaj_sucursal"], 
             'id'  => $elem["MCaj_idWEB"], 
             'codigo'  => $elem["MCaj_Codigo"] ."-".$elem["MCOD_Descripcion"] . $txtRinde ,
             'monto'  => number_format($elem["MCaj_Monto"],0,"","."), 
             'moneda'  => $elem["MCaj_Moneda"], 
             'mtopesos'  => number_format($mtoPesos,0,"","."),
             'fecha'  => $elem["f"], 
             'tipoOT'  => $elem["mdes_tipoOT"] ,
             'idfac'  => $elem["mdes_idfac"] ,
             'descri'  => $elem["mdes_descripcion"] 
            );                    
        } // Fin si es codigo
    }

  // Enviar la respuesta Ok.
  $res = [
            "success" => TRUE,
            "results" => $datostabla
  ];

    return response()->json($res);

} // Fin infmov_detalle_proceso

public function codmov()
    {     
        return view('estadisticas.codmov');
}

public function codmov_proceso()
  {

// Comparativo en 1 año de dos tipos de Informe
// ===========================
$moneda = $_GET['moneda'];
if (  $moneda == 2) {
    $ret = $this->carga_cotiza_dolar( );
    if ( $ret != ""){
        dd("Error; Al cargar cotizaciones: " . $ret );
    };    
}

if ($_GET["action"] == "informe_portipo_dif") {

    $sucursal = $_GET["sucursal"];
    $tipo_informe = $_GET["tipo_inf"];
    $anio1 = $_GET["anio1"];
    
    // Carga los vectores según los codigos seleccionados
    $codigos = [];    ;
    $codigos2 = [];          
    if (isset($_GET["codigos"] )) { 
        $param_codigos = $_GET["codigos"];
        foreach ($param_codigos as $elem) {
            //displaylog ( $elem  );
            $codigos += [ "{$elem}" => 0];
            $codigos ["{$elem}"]= 1; // Como rendimiento asumo 1
        } 
    } 
    if (isset($_GET["codigos2"])) { 
        $param_codigos2 = $_GET["codigos2"];
        foreach ($param_codigos2 as $elem) {
            //displaylog ( $elem  );
            $codigos2 += [ "{$elem}" => 0];
            $codigos2 ["{$elem}"]= 1; // Como rendimiento asumo 1
        } 
    } 

    // Carga los vectores según el tipo de informe
    // $codigos = [];    ;
    // $codigos2 = [];      
  //  include ('../common/codigos_informe.php');
    
    $filtro = " where year(Mcaj_fecMov)=" . $anio1  ;            
    if ($sucursal != 0 ) {
      $filtro = $filtro . " and mcaj_sucursal = " . $sucursal;
    }
 //   $consulta= "SELECT MCaj_Codigo,MCOD_Descripcion,MCaj_Moneda,MCaj_Monto,Mcod_HyD,Mcaj_fecMov, Month(Mcaj_fecMov) as mesbd, year(Mcaj_fecMov) as aniobd FROM McajaConDescri " . $filtro;
    $consulta= "SELECT MCaj_Codigo,MCaj_Moneda,MCaj_Monto,Mcaj_fecMov, Month(Mcaj_fecMov) as mesbd, year(Mcaj_fecMov) as aniobd , date(Mcaj_fecMov) as fecha  FROM mcaja " . $filtro;
 
    $resbd = DB::select($consulta ); 
   
    $mtosmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    
    foreach ($resbd as $row) {
        $elem = (array) $row ;  // Para adaptar a la vs que ya tenia
        if (array_key_exists($elem["MCaj_Codigo"], $codigos)) {
            $rinde =  $codigos[$elem["MCaj_Codigo"]];
            // Si la moneda no es pesos , buscar cotizacion
            $mtoPesos = cotizacion::mtoEnPesos( $elem["MCaj_Moneda"], $elem["MCaj_Monto"], $elem["Mcaj_fecMov"]) * $rinde;
            if (  $moneda == 2) {
                $cotiza = $this->buscar_cotiza_dolar( $row->fecha );
                $mtoPesos = $mtoPesos / $cotiza;
            }        

            $mtosmes[$elem["mesbd"]-1] = $mtosmes[$elem["mesbd"]-1] +  $mtoPesos;
        } // Fin si es codigo
        if (array_key_exists($elem["MCaj_Codigo"], $codigos2)) {
            $rinde =  $codigos2[$elem["MCaj_Codigo"]];
            // Si la moneda no es pesos , buscar cotizacion
            $mtoPesos = cotizacion::mtoEnPesos( $elem["MCaj_Moneda"], $elem["MCaj_Monto"], $elem["Mcaj_fecMov"]) * $rinde;
            if (  $moneda == 2) {
                $cotiza = $this->buscar_cotiza_dolar( $row->fecha );
                $mtoPesos = $mtoPesos / $cotiza;
            }        
            $mtosmes2[$elem["mesbd"]-1] = $mtosmes2[$elem["mesbd"]-1] + $mtoPesos;
        } // Fin si es codigo
    }

    // Armo respuesta en un vector
    $datos = [];
    $datostabla = [];
    $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
    $total = 0;
    $total2 = 0;
    
    // Recorro por mes
    for($i = 1; $i < 13; $i++){
        // Diferencia con el año anterior
        $dif = ($mtosmes[$i - 1] - $mtosmes2[$i - 1] ) ;

        // % De Ahorro sobre Ingreso
        $difmes = 0;
        
        if ($mtosmes[$i - 1] > 0 or $mtosmes2[$i - 1] > 0 ) {
          if ($mtosmes[$i - 1] > 0 ) {
            $difmes = ( $dif /  ($mtosmes[$i - 1] ) )  * 100;
          }  
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"",""),
             'b'  => number_format($mtosmes2[$i - 1],0,"","") );
          $datostabla[] = array(
             'mes'  => $i,
             'periodo'  => $mes[$i - 1],
             'valor1'  => number_format($mtosmes[$i - 1],0,",","."),
             'valor2'  => number_format($mtosmes2[$i - 1],0,",","."),
             'dif'  => number_format($dif, 0, ",", "."),
             'difmes'  => number_format($difmes, 2, ",", ".")." %");
          }else{
          $datos[] = array(
             'y'  => $mes[$i - 1]);
          $datostabla[] = array(
             'mes'  => $i,
             'periodo'  => $mes[$i - 1] ,
             'valor1'  => '',
             'valor2'  => '');
        }  
        $total = $total + $mtosmes[$i - 1];
        $total2 = $total2 + $mtosmes2[$i - 1];
    }  // Fin For Mes
    // Totales
    $datostabla[] = array(
         'mes'  => 13,
         'periodo'  => '<b>T O T A L</b>',
         'valor1'  => number_format($total,0,",","."),
         'valor2'  => number_format($total2,0,",","."),
         'dif'  => number_format($total - $total2, 0, ",", "."));

    $res = [
        "grafico" => $datos,
        "tabla" => $datostabla
    ];
    
} // Fin Accion

// INFORME INTERANUALES POR GRUPO DE CODIGOS DE MOVIMIENTOS
// ======================
if ($_GET["action"] == "informe_portipo") {

    $sucursal = $_GET["sucursal"];
    $tipo_informe = $_GET["tipo_inf"];
    $param_codigos = $_GET["codigos"];
    $anio1 = $_GET["anio1"];
    $anio2 = $_GET["anio2"];
   
    // Carga los vectores según el tipo de informe
    // $codigos = [];     y    $rendimento = [];
    $codigos = [];     
    $codigos2 = [];       
    $filtro2 = '';
    $ret = $this->codigos_informe($tipo_informe,$codigos,$codigos2,$filtro2,$param_codigos);
    $filtro = " where (year(Mcaj_fecMov)=" . $anio1 ." or year(Mcaj_fecMov)=".$anio2 .")";            
    if ($sucursal != 0 ) {
      $filtro = $filtro . " and mcaj_sucursal = " . $sucursal;
    }
    
 // Mas rapido solo 1 tabla  mINIMAMENTE mas rapido haciendo con el join de 2 tablas
 //      $consulta= "SELECT MCaj_Codigo,MCOD_Descripcion,MCaj_Moneda,MCaj_Monto,Mcod_HyD,Mcaj_fecMov, Month(Mcaj_fecMov) as mesbd, year(Mcaj_fecMov) as aniobd FROM McajaConDescri " . $filtro;
//    $consulta= "SELECT MCaj_Codigo,MCOD_Descripcion,MCaj_Moneda,MCaj_Monto,Mcod_HyD,Mcaj_fecMov, Month(Mcaj_fecMov) as mesbd, year(Mcaj_fecMov) as aniobd FROM MCodigo INNER JOIN Mcaja ON MCodigo.MCod_Codigo = Mcaja.MCaj_Codigo " . $filtro;
    $consulta= "SELECT MCaj_Sucursal,MCaj_Codigo,MCaj_Moneda,MCaj_Monto,Mcaj_fecMov, Month(Mcaj_fecMov) as mesbd, year(Mcaj_fecMov) as aniobd , date(Mcaj_fecMov) as fecha  FROM mcaja " . $filtro;
    
    $resbd = DB::select($consulta); 

//    dd($consulta,$filtro,$resbd);

    $mtosmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    
    foreach ($resbd as $row) {
        $elem = (array) $row ;  // Para adaptar a la vs que ya tenia
        if (array_key_exists($elem["MCaj_Codigo"], $codigos)) {
            $rinde =  $codigos[$elem["MCaj_Codigo"]];
            // Si la moneda no es pesos , buscar cotizacion
            $mtoPesos = cotizacion::mtoEnPesos( $elem["MCaj_Moneda"], $elem["MCaj_Monto"], $elem["Mcaj_fecMov"]) * $rinde;
            if (  $moneda == 2) {
                $cotiza = $this->buscar_cotiza_dolar( $row->fecha );
                $mtoPesos = $mtoPesos / $cotiza;
            }        
            if($anio1 == $elem["aniobd"] ){    
                $mtosmes[$elem["mesbd"]-1] = $mtosmes[$elem["mesbd"]-1] +  $mtoPesos;
            }else{
                $mtosmes2[$elem["mesbd"]-1] = $mtosmes2[$elem["mesbd"]-1] + $mtoPesos;
            }
        } // Fin si es codigo 
    }

    // Armo respuesta en un vector
    $datos = [];
    $datostabla = [];
    $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
    $total = 0;
    $total2 = 0;
    // Recorro por mes
    for($i = 1; $i < 13; $i++){
        // Diferencia con el año anterior
        $dif = 0;
        if($mtosmes[$i - 1]> 0) {
            $dif = ($mtosmes2[$i - 1] - $mtosmes[$i - 1] ) / $mtosmes[$i - 1] * 100;
        }

        // Diferencia con el mes anterior
        $difmes = 0;
        if($i == 1 ) {
           // Enero comparo con diciembre del año anterior 
            if($mtosmes[11]> 0) {
                $difmes = ($mtosmes2[$i - 1] - $mtosmes[11] )  / $mtosmes[11] * 100;}
        }else{
            if($mtosmes2[$i - 2]> 0) {
                $difmes = ($mtosmes2[$i - 1] - $mtosmes2[$i - 2] )  / $mtosmes2[$i - 2] * 100;}
        }        
       
        if ($mtosmes2[$i - 1] > 0 ) {
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"",""),
             'b'  => number_format($mtosmes2[$i - 1],0,"","") );
        }else{
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"","") );
        }  
        $datostabla[] = array(
         'mes'  => $i,
         'periodo'  => $mes[$i - 1],
         'valor1'  => number_format($mtosmes[$i - 1],0,",","."),
         'valor2'  => number_format($mtosmes2[$i - 1],0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'difmes'  => number_format($difmes, 2, ",", ".")." %");
        $total = $total + $mtosmes[$i - 1];
        $total2 = $total2 + $mtosmes2[$i - 1];
    }  // Fin For Mes

    // Totales
    if ($total == 0 ) {
      $dif = 0;
    }else{  
      $dif = ($total2 - $total )  / $total * 100;
    }
    $datostabla[] = array(
         'mes'  => 13,
         'periodo'  => '<b>T O T A L</b>',
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'valor1'  => number_format($total,0,",","."),
         'valor2'  => number_format($total2,0,",","."));
  
  $res = [
    "grafico" => $datos,
    "tabla" => $datostabla
  ];
    
} // Fin Accion

// Informe de Barras,   por Nivele 1  de Codigos 
// =================
if ($_GET["action"] == "informe_barra") {

    $tipo_informe = $_GET["tipo_inf"];
    $anio = $_GET["anio1"];

    
    // Segun el tipo de informe tomo los codigos
    $filtro_cod = "";
    switch ($tipo_informe) {
        case 12:  // Ventas porSector
            $filtro_cod = "H";
            break;
        case 13:  // Compras por Sector
            $filtro_cod = "D";
            break;
    }
       
    $filtro = " where year(Mcaj_fecMov)=" . $anio ;            
    $consulta= "SELECT MCaj_Codigo,MCod_Nivel1,MCaj_Moneda,MCaj_Monto,Mcod_HyD,Mcaj_fecMov, Month(Mcaj_fecMov) as mesbd, year(Mcaj_fecMov) as aniobd FROM McajaConDescri " . $filtro;
    
    $resbd = DB::select($consulta ); 
 
    $mtosmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes3 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    
    foreach ($resbd as $objelem) {
        $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
        if ($elem["MCaj_Codigo"] <> '0900') {
        if ( ( $elem["Mcod_HyD"] == $filtro_cod)  or ( $tipo_informe == 14 )   ) {

            // Si la moneda no es pesos , buscar cotizacion

            $mtoPesos = cotizacion::mtoEnPesos( $elem["MCaj_Moneda"], $elem["MCaj_Monto"], $elem["Mcaj_fecMov"]) ;
            if ( ($elem["Mcod_HyD"] == "D")  and ($tipo_informe == 14 ) ) {
                $mtoPesos = $mtoPesos * -1;
            }
            switch ($elem["MCod_Nivel1"]) {
                case 'O':  //Optica
                    $mtosmes[$elem["mesbd"]-1] = $mtosmes[$elem["mesbd"]-1] +  $mtoPesos;
                    break;
                case 'F':  // Foto
                    $mtosmes2[$elem["mesbd"]-1] = $mtosmes2[$elem["mesbd"]-1] +  $mtoPesos;
                    break;
                case 'A':  // En comun
                    $mtosmes3[$elem["mesbd"]-1] = $mtosmes3[$elem["mesbd"]-1] +  $mtoPesos;
                    break;
            }
          //  $codigos[$elem["MCaj_Codigo"]]= $codigos[$elem["MCaj_Codigo"]] + $mtoPesos ;
        } // Fin si es codigo
        } // Fin si es codigo <> 900  Trasferencia
    }

    // Armo respuesta en un vector
    $datos = [];
    $datostabla = [];
    $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
    
    // Recorro por mes
    for($i = 1; $i < 13; $i++){
        // % del total por rubro
        $total = ($mtosmes3[$i - 1] + $mtosmes2[$i - 1]  + $mtosmes[$i - 1] );            
        // Diferencia con el mes anterior
        $porcenta = 0;
        $difmes = 0;
        if($total> 0) {
           $difmes = ($mtosmes[$i - 1] )  / $total * 100;
        }      
        if ($total <> 0 ) {
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"",""),
             'b'  => number_format($mtosmes2[$i - 1],0,"",""),
             'c'  => number_format($mtosmes3[$i - 1],0,"","") );
        }else{
          $datos[] = array(
             'y'  => $mes[$i - 1] );
        }  
        $datostabla[] = array(
         'periodo'  => $mes[$i - 1],
         'valor1'  => number_format($mtosmes[$i - 1],0,"",""),
         'valor2'  => number_format($mtosmes2[$i - 1],0,"",""),
         'valor3'  => number_format($mtosmes3[$i - 1],0,"",""),
         'total'  => number_format($total,0,"",""),
         'difmes'  => number_format($difmes, 2, ",", ".")." %");
    }  // Fin For Mes
    $res = [
            "grafico" => $datos,
            "tabla" => $datostabla
    ];
    
} // Fin Accion Informe Barra


    return response()->json($res);

    } // Fin Proceso codmov
    
    public function codigos_codmov_informe() {


        $tipo_informe = "";
        if(isset($_GET['informe'])){
            $tipo_informe = $_GET['informe'];
        }    
        $datos = minformecod::buscar($tipo_informe ) ;
    
        $res = array( "data"=>   $datos);
        return response()->json($res);
    
    } // Fin combo_codmov_informe

        
    public function combo_codmov_informe() {

    $html = "";
    $html2 = "";
    
    if(isset($_GET['informe'])){
        $tipo_informe = $_GET['informe'];
        
        // Carga los vectores según el tipo de informe
        // $codigos = [];     y    $rendimento = [];
        $codigos = [];     
        $codigos2 = [];
        $param_codigos ='';       
        $filtro2 = '';
        $ret = $this->codigos_informe($tipo_informe,$codigos,$codigos2,$filtro2,$param_codigos);

        $consulta = "SELECT MCod_Codigo,MCod_Descripcion,MCOD_HYD  FROM mcodigo   WHERE   MCod_Estado<>'I' ORDER BY MCOD_HYD Desc, MCod_Codigo";

        $html .= '<optgroup label="CREDITOS / VENTAS">';
        $ant_MCOD_HYD = "H";
        $resbd = DB::select($consulta ); 
        foreach ($resbd as $objelem) {
            $row = (array) $objelem ;  // Para adaptar a la vs que ya tenia
            if($ant_MCOD_HYD != $row["MCOD_HYD"] ) {
                $ant_MCOD_HYD = $row["MCOD_HYD"];
                $html .= '<optgroup label="DEBITOS / GASTOS">';
            }
            if (array_key_exists($row["MCod_Codigo"], $codigos)) {
                $html .= '<option value= "' . $row["MCod_Codigo"] . '"selected>' . $row["MCod_Codigo"] ." " .  $row["MCod_Descripcion"] . '</option>';

            }else{
                $html .= '<option value= "' . $row["MCod_Codigo"] . '">' . $row["MCod_Codigo"] ." ".
                 $row["MCod_Descripcion"] . '</option>';
            }
        }                
        if($filtro2 != '') {
            $consulta = "SELECT MCod_Codigo,MCod_Descripcion,MCOD_HYD  FROM mcodigo  WHERE   MCod_Estado<>'I' ORDER BY MCOD_HYD Desc, MCod_Codigo";
            $html2 .= '<optgroup label="CREDITOS / VENTAS">';
            $ant_MCOD_HYD = "H";
            $resbd = DB::select($consulta ); 
            foreach ($resbd as $objelem) {
                $row = (array) $objelem ;  // Para adaptar a la vs que ya tenia
                if($ant_MCOD_HYD != $row["MCOD_HYD"] ) {
                    $ant_MCOD_HYD = $row["MCOD_HYD"];
                    $html2 .= '<optgroup label="DEBITOS / GASTOS">';
                }
                if (array_key_exists($row["MCod_Codigo"], $codigos2)) {
                    $html2 .= '<option value= "' . $row["MCod_Codigo"] . '"selected>' . $row["MCod_Codigo"] ." " .  $row["MCod_Descripcion"] . '</option>';
                }else{
                    $html2 .= '<option value= "' . $row["MCod_Codigo"] . '">' . $row["MCod_Codigo"] ." " .  $row["MCod_Descripcion"] . '</option>';
                }
            }                
        }
    }

    $res = array("html"=>$html,"html2"=>$html2);
    return response()->json($res);

    } // Fin combo_codmov_informe



    private  function codigos_informe($id_informe,&$codigos,&$codigos2,&$filtro2,$param_codigos)
    {

    // Carga los vectores según el tipo de informe
    $codigos = [];     
    $codigos2 = [];

    if($id_informe == 19) { // Eligio los codigos personalizados
       if (isset($param_codigos )) { 
       if ($param_codigos != '' ) { 
        foreach ($param_codigos as $elem) {
            //displaylog ( $elem  );
            $codigos += [ "{$elem}" => 0];
            $codigos ["{$elem}"]= 1; // Como rendimiento asumo 1
        } 
       } 
       }
    }else{
        // Segun el  informe tomo los codigos
        $filtro = " where infCod_IdInforme = " . $id_informe;
        $informe = minforme::find($id_informe);
        if ($informe->inf_tipo == 2 ) {  // Por diferencia de Informes
            $filtro = " where infCod_IdInforme = " . $informe->inf_info1;
            $filtro2 = " where infCod_IdInforme = " . $informe->inf_info2;
        }
        /*
        switch ($tipo_informe) {
            case 4:  // Ingresos totales = Ing Optica (1) + Ing Foto (3) + Ing Com 20 + Ing Cel 30
                $filtro = " where infCod_IdInforme in(1,3,20,30)";
                break;
            case 9:  // Rendimiento totales
                $filtro = " where infCod_IdInforme in(6,7,22)";
                break;
            case 10:  // Comunes  Ventas  Vs   Compras 
                $filtro = " where infCod_IdInforme in(20)"; // Rendimiento totales
                $filtro2 = " where infCod_IdInforme in(21)"; // Gastos totales + retiros
                break;
            case 17:  // Foto  Ventas  Vs   Compras 
                $filtro = " where infCod_IdInforme in(3)"; // Ventas
                $filtro2 = " where infCod_IdInforme in(15)"; // Compras
                break;
            case 18:  // Optica  Ventas  Vs   Compras 
                $filtro = " where infCod_IdInforme in(1)"; // Ventas
                $filtro2 = " where infCod_IdInforme in(16)"; // Compras
                break;
            case 5:  // Gastos totales
                $filtro = " where infCod_IdInforme in(2,8)";
                break;    }
        */

        $consulta= "SELECT infCod_Codigo, infCod_Rendimiento  FROM minformecod" . $filtro;    
        $resbd = DB::select($consulta ); 
        foreach ($resbd as $objelem) {
            $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
            //displaylog ( $elem ["infCod_Codigo"] . " Rend:" . $elem ["infCod_Rendimiento"] );
            $codigos += [ "{$elem ["infCod_Codigo"]}" => 0];
            $codigos ["{$elem ["infCod_Codigo"]}"]= $elem ["infCod_Rendimiento"]; // En el valor se carga el rendimiento
        }        
        if($filtro2 != '') {
            $consulta= "SELECT infCod_Codigo, infCod_Rendimiento  FROM minformecod" . $filtro2;    
            $resbd = DB::select($consulta ); 
            foreach ($resbd as $objelem) {
                 $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
                //displaylog ( $elem ["infCod_Codigo"] . " Rend:" . $elem [1] );
                $codigos2 += [ "{$elem ["infCod_Codigo"]}" => 0];
                $codigos2 ["{$elem ["infCod_Codigo"]}"]= $elem ["infCod_Rendimiento"]; // En el valor se carga el rendimiento
            }                    
        }
    } // Si es personalizado o por tipo de Informe

    } // Fin Funcion


    public function rubro()
    {     
    
        $sucursales = sucursal::combo(Auth::user()->sucursal , 'S' ); //Incluye todas
        $familias = familia::select(DB::raw("CONCAT( Flia_Id ,' - ',Flia_Descripcion) as descri"),'Flia_Id')->orderBy('Flia_Id', 'ASC')->where( 'Flia_estado','!=','I')->pluck( 'descri','Flia_Id'); 

        return view('estadisticas.rubro' , [ 'familias' => $familias , 'sucursales' => $sucursales ] );

    }


    public function rubro_proceso()
    {

// INFORME INTERANUALES POR FAMILIAS DE PRODUCTOS
// ======================
if ($_GET["action"] == "informe_rubro_cant") {

    // Tomo los parametros de Entrada 
    $sucursal = $_GET["sucursal"];
    $rubro = $_GET["tipo_inf"];
    $anio1 = $_GET["anio1"];
    $anio2 = $_GET["anio2"];
    $operacion = $_GET["operacion"]; 
    $id_producto = $_GET["id_producto"]; 
    $desc_producto = $_GET["desc_producto"]; 
   
    if ( $operacion == "V" ) { // V = Ventas + R = Anulaciones
      $filtro = " where  ( year(Mov_fecMov)=" . $anio1 ." or year(Mov_fecMov)=" . $anio2 . " ) and Mov_Familia = '" . $rubro . "' and  (Mov_operacion = '" . $operacion . "' or Mov_operacion = 'R' ) and mov_idprod <> '0'" ;
    }else{  
      $filtro = " where  ( year(Mov_fecMov)=" . $anio1 ." or year(Mov_fecMov)=" . $anio2 . " ) and Mov_Familia = '" . $rubro . "' and  (Mov_operacion = '" . $operacion . "' ) and mov_idprod <> '0'" ;
    }

    if ($sucursal != 0 ) {
      $filtro = $filtro . " and mov_sucursal = " . $sucursal;
    }

    if ($id_producto != '' ) {
      $filtro = $filtro . " and mov_idprod = '" . $id_producto . "'";
    }

    if ($desc_producto != '' ) {
      $filtro = $filtro . " and prod_descripcion LIKE '%" . $desc_producto . "%'";
    }

//    $consulta= "SELECT Mov_IdProd, Mov_Cantidad,Mov_precio,  Month(Mov_fecMov) as mesbd, year(Mov_fecMov) as aniobd , mov_operacion  FROM moviproductos " . $filtro;
   
    $consulta= "SELECT Mov_IdProd, Mov_Cantidad,Mov_precio,  Month(Mov_fecMov) as mesbd, year(Mov_fecMov) as aniobd , mov_operacion  FROM productos INNER JOIN moviproductos ON (productos.Prod_Id = moviproductos.Mov_IdProd) AND (productos.Prod_Familia = moviproductos.Mov_Familia) " . $filtro;


    $resbd = DB::select($consulta ); 

    $mtosmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $cantmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $cantmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    
    // Recorro el resultado de la Bd y los cargo en vectores por mes
    foreach ($resbd as $objelem) {
        $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia

         // Casos de despuntos por Lentes de Contacto 
        if($elem["Mov_IdProd"] == 'par' or $elem["Mov_IdProd"] == 'caja' ){
            $elem["Mov_Cantidad"] = 0;
        }
        if($elem["mov_operacion"] == 'V') { // Ventas
            if($anio1 == $elem["aniobd"] ){    
                $mtosmes[$elem["mesbd"]-1] = $mtosmes[$elem["mesbd"]-1] +  $elem["Mov_precio"];
                $cantmes[$elem["mesbd"]-1] = $cantmes[$elem["mesbd"]-1] +  ( $elem["Mov_Cantidad"] * -1 );
            }else{
                $mtosmes2[$elem["mesbd"]-1] = $mtosmes2[$elem["mesbd"]-1] +  $elem["Mov_precio"];
                $cantmes2[$elem["mesbd"]-1] = $cantmes2[$elem["mesbd"]-1] +  ( $elem["Mov_Cantidad"] * -1);
            }
        }elseif($elem["mov_operacion"] == 'R') { // Anulacion Venta
  //        dd($elem["mov_operacion"] ,  $elem["Mov_Cantidad" ] , $cantmes[$elem["mesbd"]-1], $cantmes2[$elem["mesbd"]-1]);

            if($anio1 == $elem["aniobd"] ){    
                $mtosmes[$elem["mesbd"]-1] = $mtosmes[$elem["mesbd"]-1] +  $elem["Mov_precio"];
                $cantmes[$elem["mesbd"]-1] = $cantmes[$elem["mesbd"]-1] -  $elem["Mov_Cantidad"];
            }else{
                $mtosmes2[$elem["mesbd"]-1] = $mtosmes2[$elem["mesbd"]-1] + $elem["Mov_precio"];
                $cantmes2[$elem["mesbd"]-1] = $cantmes2[$elem["mesbd"]-1] - $elem["Mov_Cantidad"] ;
            }

        }else{ // Compras
          if($elem["mov_operacion"]  == 'C') { // Las anoulaciones no la tenemos en cuenta si es compra
            if($anio1 == $elem["aniobd"] ){    
                $mtosmes[$elem["mesbd"]-1] = $mtosmes[$elem["mesbd"]-1] +  $elem["Mov_precio"] * -1;
                $cantmes[$elem["mesbd"]-1] = $cantmes[$elem["mesbd"]-1] +  ( $elem["Mov_Cantidad"] );
            }else{
                $mtosmes2[$elem["mesbd"]-1] = $mtosmes2[$elem["mesbd"]-1] +  $elem["Mov_precio"] * -1;
                $cantmes2[$elem["mesbd"]-1] = $cantmes2[$elem["mesbd"]-1] +  ( $elem["Mov_Cantidad"] );
            }
          }              
        }    
    }

    // Armo respuesta desde los  vectores, para los graficos y tablas
    $datos_cant = [];
    $datostabla_cant = [];
    $datos = [];
    $datostabla = [];
    $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
    $total_cant = 0;
    $total2_cant = 0;
    $total = 0;
    $total2 = 0;
    // Recorro vectores por mes
    for($i = 1; $i < 13; $i++){
        // Diferencia con el año anterior
        $dif_cant = 0;
        $dif = 0;
        if($mtosmes[$i - 1]> 0) {
            $dif_cant = ($cantmes2[$i - 1] - $cantmes[$i - 1] )  / $cantmes[$i - 1] * 100;
            $dif = ($mtosmes2[$i - 1] - $mtosmes[$i - 1] )  / $mtosmes[$i - 1] * 100;}

        // Diferencia con el mes anterior
        $difmes_cant = 0;
        $difmes = 0;
        if($i == 1 ) {
           // Enero comparo con diciembre del año anterior 
            if($mtosmes[11]> 0) {
                $difmes_cant = ($cantmes2[$i - 1] - $cantmes[11] )  / $cantmes[11] * 100;
                $difmes = ($mtosmes2[$i - 1] - $mtosmes[11] )  / $mtosmes[11] * 100;}
        }else{
            if($mtosmes2[$i - 2]> 0) {
                $difmes_cant = ($cantmes2[$i - 1] - $cantmes2[$i - 2] )  / $cantmes2[$i - 2] * 100;
                $difmes = ($mtosmes2[$i - 1] - $mtosmes2[$i - 2] )  / $mtosmes2[$i - 2] * 100;}
        }        
       
        if ($mtosmes2[$i - 1] > 0 ) {
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"",""),
             'b'  => number_format($mtosmes2[$i - 1],0,"","") );
          $datos_cant[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($cantmes[$i - 1],0,"",""),
             'b'  => number_format($cantmes2[$i - 1],0,"","") );
        }else{
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"","") );
          $datos_cant[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($cantmes[$i - 1],0,"","") );
        }  

        $datostabla_cant[] = array(
         'mes'  => $i,
         'periodo'  => $mes[$i - 1],
         'valor1'  => number_format($cantmes[$i - 1],0,",","."),
         'valor2'  => number_format($cantmes2[$i - 1],0,",","."),
         'dif'  => number_format($dif_cant, 2, ",", ".")." %",
         'difmes'  => number_format($difmes_cant, 2, ",", ".")." %");
        $total_cant = $total_cant + $cantmes[$i - 1];
        $total2_cant = $total2_cant + $cantmes2[$i - 1];

        $datostabla[] = array(
         'mes'  => $i,
         'periodo'  => $mes[$i - 1],
         'valor1'  => number_format($mtosmes[$i - 1],0,",","."),
         'valor2'  => number_format($mtosmes2[$i - 1],0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'difmes'  => number_format($difmes, 2, ",", ".")." %");
        $total = $total + $mtosmes[$i - 1];
        $total2 = $total2 + $mtosmes2[$i - 1];
    }  // Fin For Mes
    // Totales
    if ($total_cant > 0 ) {
        $dif = ($total2_cant - $total_cant )  / $total_cant * 100; } else { $dif =0; }
    $datostabla_cant[] = array(
         'mes'  => 13,
         'periodo'  => '<b>T O T A L</b>',
         'valor1'  => number_format($total_cant,0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'valor2'  => number_format($total2_cant,0,",","."));

    if ($total > 0 ) {
       $dif = ($total2 - $total )  / $total * 100; }else { $dif=0; }
    $datostabla[] = array(
         'mes'  => 13,
         'periodo'  => '<b>T O T A L</b>',
         'valor1'  => number_format($total,0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'valor2'  => number_format($total2,0,",","."));
  
  $res = [
    "grafico_cant" => $datos_cant,
    "tabla_cant" => $datostabla_cant,
    "grafico" => $datos,
    "tabla" => $datostabla
  ];
    

} // Fin Accion


    return response()->json($res);

    } // Fin Proceso rubro


    public function ot()
    {     
        return view('estadisticas.ot');
    }


    public function ot_proceso()
    {


//  INFORME INTER ANULA DE OT    
if ($_GET["action"] == "ot_interanualNvo") {

    // Armo respuesta en un vector
    $res = [];
    // Tomo los parametros de entrada
    $sucursal = $_GET["sucursal"];
    $tipo_ot = $_GET["tipo_ot"];
    $tipo_obr = $_GET["tipo_obr"];
    $medico = $_GET["medico"];
    $vendedor = $_GET["vendedor"];
    $anio1 = $_GET["anio1"];
    $anio2 = $_GET["anio2"];
    $filtro = " WHERE OT_TIPO='" . $tipo_ot . "' AND OT_ESTADO<>'A' and ( year(ot_fecpedido)=" . $anio1 ." or year(ot_fecpedido)=" . $anio2 . " ) ";
    $from = "ot";
    switch ($tipo_obr) {
        case 2:  // Particular 
            $filtro =  $filtro . " and ot_obrid = 'PARTI'"; 
            break;
        case 3:  // PAMI
            $filtro =  $filtro . " and ot_obrid = 'PAMI'"; 
            break;
    }
    if($sucursal != 0 ){
        $filtro = $filtro . " and ot_sucursal = '$sucursal'";
    }

    if($vendedor != "1" ){
        $filtro = $filtro . " and ot_vendedor = '$vendedor'";
    }

    if($medico != " " ){
        $from = "ot  INNER JOIN ot_ant ON ot.OT_ID = ot_ant.OTANT_ID";
        $filtro = $filtro . " and otant_idmedico = $medico";
    }
    
    $consulta= "SELECT ot_precio,  Month(ot_fecpedido) as mesbd, year(ot_fecpedido) as aniobd  FROM " . $from . $filtro;

    $resbd = DB::select($consulta ); 
    
    $mtosmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $cantmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $cantmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    
    // Recorro el resultado de la Bd y los cargo en vectores por mes
    foreach ($resbd as $objelem) {
            $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
            if($anio1 == $elem["aniobd"] ){    
                $mtosmes[$elem["mesbd"]-1] = $mtosmes[$elem["mesbd"]-1] +  $elem["ot_precio"];
                $cantmes[$elem["mesbd"]-1] = $cantmes[$elem["mesbd"]-1] +  1;
            }else{
                $mtosmes2[$elem["mesbd"]-1] = $mtosmes2[$elem["mesbd"]-1] +  $elem["ot_precio"];
                $cantmes2[$elem["mesbd"]-1] = $cantmes2[$elem["mesbd"]-1] +  1;
            }
    }

    // Armo respuesta desde los  vectores, para los graficos y tablas
    $datos_cant = [];
    $datostabla_cant = [];
    $datos = [];
    $datostabla = [];
    $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
    $total_cant = 0;
    $total2_cant = 0;
    $total = 0;
    $total2 = 0;    // Recorro vectores por mes
    for($i = 1; $i < 13; $i++){
        // Diferencia con el año anterior
        $dif_cant = 0;
        $dif = 0;
        if($mtosmes[$i - 1]> 0) {
            $dif_cant = ($cantmes2[$i - 1] - $cantmes[$i - 1] )  / $cantmes[$i - 1] * 100;
            $dif = ($mtosmes2[$i - 1] - $mtosmes[$i - 1] )  / $mtosmes[$i - 1] * 100;}

        // Diferencia con el mes anterior
        $difmes_cant = 0;
        $difmes = 0;
        if($i == 1 ) {
           // Enero comparo con diciembre del año anterior 
            if($mtosmes[11]> 0) {
                $difmes_cant = ($cantmes2[$i - 1] - $cantmes[11] )  / $cantmes[11] * 100;
                $difmes = ($mtosmes2[$i - 1] - $mtosmes[11] )  / $mtosmes[11] * 100;}
        }else{
            if($mtosmes2[$i - 2]> 0) {
                $difmes_cant = ($cantmes2[$i - 1] - $cantmes2[$i - 2] )  / $cantmes2[$i - 2] * 100;
                $difmes = ($mtosmes2[$i - 1] - $mtosmes2[$i - 2] )  / $mtosmes2[$i - 2] * 100;}
        }        
       
        if ($mtosmes2[$i - 1] > 0 ) {
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"",""),
             'b'  => number_format($mtosmes2[$i - 1],0,"","") );
          $datos_cant[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($cantmes[$i - 1],0,"",""),
             'b'  => number_format($cantmes2[$i - 1],0,"","") );
        }else{
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"","") );
          $datos_cant[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($cantmes[$i - 1],0,"","") );
        }  

        $datostabla_cant[] = array(
         'mes'  => $i,
         'periodo'  => $mes[$i - 1],
         'valor1'  => number_format($cantmes[$i - 1],0,",","."),
         'valor2'  => number_format($cantmes2[$i - 1],0,",","."),
         'dif'  => number_format($dif_cant, 2, ",", ".")." %",
         'difmes'  => number_format($difmes_cant, 2, ",", ".")." %");
        $total_cant = $total_cant + $cantmes[$i - 1];
        $total2_cant = $total2_cant + $cantmes2[$i - 1];

        $datostabla[] = array(
         'mes'  => $i,
         'periodo'  => $mes[$i - 1],
         'valor1'  => number_format($mtosmes[$i - 1],0,",","."),
         'valor2'  => number_format($mtosmes2[$i - 1],0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'difmes'  => number_format($difmes, 2, ",", ".")." %");
        $total = $total + $mtosmes[$i - 1];
        $total2 = $total2 + $mtosmes2[$i - 1];
    }  // Fin For Mes
    // Totales
    $dif = 0;
    if ( $total_cant > 0) {
      $dif = ($total2_cant - $total_cant )  / $total_cant * 100;
    }
    $datostabla_cant[] = array(
         'mes'  => 13,
         'periodo'  => '<b>T O T A L</b>',
         'valor1'  => number_format($total_cant,0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'valor2'  => number_format($total2_cant,0,",","."));

    $dif = 0;
    if ( $total > 0) {
        $dif = ($total2 - $total )  / $total * 100;
    }
    $datostabla[] = array(
         'mes'  => 13,
         'periodo'  => '<b>T O T A L</b>',
         'valor1'  => number_format($total,0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'valor2'  => number_format($total2,0,",","."));


    $res = [
     "grafico_cant" => $datos_cant,
     "tabla_cant" => $datostabla_cant,
     "grafico_mto" => $datos,
     "tabla" => $datostabla
    ];

    
} // Fin Accion


    return response()->json($res);

    } // Fin Proceso ot


    public function iva()
    {     
        return view('estadisticas.iva');
    }


    public function iva_proceso()
    {
     
// Comporativo en 1 año entre Venta y Compras
// ===========================
if ($_GET["action"] == "informe_portipo_dif") {

    $tipo_mto = $_GET["tipo_mto"];
    $anio1 = $_GET["anio1"];

    $filtro = " where int(Lib_Periodo/100)=" . $anio1  ;                
    $consulta= "SELECT Lib_Periodo,lib_ComTotal,lib_VtaTotal,lib_ComIva,lib_VtaIva FROM Libros " . $filtro;  
    $resbd = DB::select($consulta ); 

      
   // $resbd=$db->ejecuta_fetchAll($consulta); // Ejecuta la consulta y retorna todos los registros

    $mtosmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    
    foreach ($resbd as $objelem) {
        $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
        $fila_anio = substr ( $elem["Lib_Periodo"],0,4);
        $fila_mes = substr ( $elem["Lib_Periodo"],4,2);
        if ($tipo_mto == "I") {
            $mtoPesos = $elem["lib_VtaIva"];        
            $mtosmes[$fila_mes - 1] = $mtosmes[$fila_mes -1] +  $mtoPesos;
            $mtoPesos = $elem["lib_ComIva"];        
            $mtosmes2[$fila_mes - 1] = $mtosmes2[$fila_mes -1] +  $mtoPesos;
        }else{
            $mtoPesos = $elem["lib_VtaTotal"];        
            $mtosmes[$fila_mes - 1] = $mtosmes[$fila_mes -1] +  $mtoPesos;
            $mtoPesos = $elem["lib_ComTotal"];        
            $mtosmes2[$fila_mes - 1] = $mtosmes2[$fila_mes -1] +  $mtoPesos;            
        }   
    }

    // Armo respuesta en un vector
    $datos = [];
    $datostabla = [];
    $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
    $totalmtosmes = 0;
    $totalmtosmes2 = 0;
    // Recorro por mes
    for($i = 1; $i < 13; $i++){
        $totalmtosmes = $totalmtosmes + $mtosmes[$i - 1];
        $totalmtosmes2 = $totalmtosmes2 + $mtosmes2[$i - 1];        
        // Diferencia
        $dif = ($mtosmes[$i - 1] - $mtosmes2[$i - 1] ) ;
        // % De Ahorro sobre Ingreso
        $difmes = 0;
        if ($mtosmes[$i - 1] > 0 or $mtosmes2[$i - 1] > 0 ) {
          if ($mtosmes[$i - 1] > 0 ) {
            $difmes = ( $dif /  ($mtosmes[$i - 1] ) )  * 100;
          }  
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"",""),
             'b'  => number_format($mtosmes2[$i - 1],0,"","") );
          $datostabla[] = array(
             'mes'  => $i,
             'periodo'  => $mes[$i - 1],
             'valor1'  => number_format($mtosmes[$i - 1],0,",","."),
             'valor2'  => number_format($mtosmes2[$i - 1],0,",","."),
             'dif'  => number_format($dif, 0, ",", "."),
             'difmes'  => number_format($difmes, 2, ",", ".")." %");
          }else{
          $datos[] = array(
             'y'  => $mes[$i - 1]);
          $datostabla[] = array(
             'mes'  => $i,
             'periodo'  => $mes[$i - 1] );
        }  
    }  // Fin For Mes

        $dif = ($totalmtosmes - $totalmtosmes2 ) ;
        // % De Ahorro sobre Ingreso
        $difmes = 0;
          if ($totalmtosmes > 0 ) {
            $difmes = ( $dif /  ($totalmtosmes ) )  * 100;
          }  

          $datostabla[] = array(
             'mes'  => 0,
             'periodo'  => 'T O T A L E S',
             'valor1'  => number_format($totalmtosmes,0,",","."),
             'valor2'  => number_format($totalmtosmes2,0,",","."),
             'dif'  => number_format($dif, 0, ",", "."),
             'difmes'  => number_format($difmes, 2, ",", ".")." %");
    
    
    
    $res = [
        "grafico" => $datos,
        "tabla" => $datostabla
    ];
    
} // Fin Accion

// INFORME INTERANUALES POR GRUPO DE CODIGOS DE MOVIMIENTOS
// ======================
if ($_GET["action"] == "informe_portipo") {

    $tipo_iva = $_GET["tipo_iva"];
    $anio1 = $_GET["anio1"];
    $anio2 = $_GET["anio2"];
   
    $filtro = " where int(Lib_Periodo/100)=" . $anio1 ." or int(Lib_Periodo/100)=" . $anio2 ;                
    $consulta= "SELECT Lib_Periodo,lib_ComTotal,lib_VtaTotal,lib_ComIva,lib_VtaIva FROM Libros " . $filtro;  
    $resbd = DB::select($consulta ); 

    $mtosmes = array(0,0,0,0,0,0,0,0,0,0,0,0);
    $mtosmes2 = array(0,0,0,0,0,0,0,0,0,0,0,0);
    if ($resbd) {
     foreach ($resbd as $objelem) {
        $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
         //  var_dump($elem);
           
  //          $fila_anio = $elem["Lib_Periodo"] / 100;
   //         $fila_periodo = explode(".",$fila_anio);

            $fila_anio = substr ( $elem["Lib_Periodo"],0,4);
            $fila_mes = substr ( $elem["Lib_Periodo"],4,2);

         //   var_dump($elem["Lib_Periodo"] );
        //    string substr ( string $string , int $start [, int $length ] )
        //    var_dump($fila_anio);
        ///                var_dump($fila_mes);

//            var_dump($fila_periodo[0]);
 //           var_dump($fila_periodo[1]);
            switch ($tipo_iva) {
                case 'V':  // Ventas
                    $mtoPesos = $elem["lib_VtaTotal"];
                    break;
                case 'C':  // Compras
                    $mtoPesos = $elem["lib_ComTotal"];
                    break;
                case 'R':  // Resulado
                    $mtoPesos = $elem["lib_VtaTotal"] - $elem["lib_ComTotal"];
                    break;
            }
            if($anio1 == $fila_anio ){    
                $mtosmes[$fila_mes - 1] = $mtosmes[$fila_mes -1] +  $mtoPesos;
            }else{
                $mtosmes2[$fila_mes -1] = $mtosmes2[$fila_mes -1] + $mtoPesos;
            }
     }
    }
    
  
    
    // Armo respuesta en un vector
    $datos = [];
    $datostabla = [];
    $mes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];  
    $totalmtosmes = 0;
    $totalmtosmes2 = 0;
    // Recorro por mes
    for($i = 1; $i < 13; $i++){
        $totalmtosmes = $totalmtosmes + $mtosmes[$i - 1];
        $totalmtosmes2 = $totalmtosmes2 + $mtosmes2[$i - 1];        
        // Diferencia con el año anterior
        $dif = 0;
        if($mtosmes[$i - 1]> 0) {
            $dif = ($mtosmes2[$i - 1] - $mtosmes[$i - 1] )  / $mtosmes[$i - 1] * 100;}

        // Diferencia con el mes anterior
        $difmes = 0;
        if($i == 1 ) {
           // Enero comparo con diciembre del año anterior 
            if($mtosmes[11]> 0) {
                $difmes = ($mtosmes2[$i - 1] - $mtosmes[11] )  / $mtosmes[11] * 100;}
        }else{
            if($mtosmes2[$i - 2]> 0) {
                $difmes = ($mtosmes2[$i - 1] - $mtosmes2[$i - 2] )  / $mtosmes2[$i - 2] * 100;}
        }        
       
        if ($mtosmes2[$i - 1] > 0 ) {
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"",""),
             'b'  => number_format($mtosmes2[$i - 1],0,"","") );
        }else{
          $datos[] = array(
             'y'  => $mes[$i - 1],
             'a'  => number_format($mtosmes[$i - 1],0,"","") );
        }  
        $datostabla[] = array(
         'mes'  => $i,
         'periodo'  => $mes[$i - 1],
         'valor1'  => number_format($mtosmes[$i - 1],0,",","."),
         'valor2'  => number_format($mtosmes2[$i - 1],0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %",
         'difmes'  => number_format($difmes, 2, ",", ".")." %");
    }  // Fin For Mes


        // Diferencia con el año anterior
        $dif = 0;
        if($totalmtosmes> 0) {
            $dif = ($totalmtosmes2 - $totalmtosmes) / $totalmtosmes * 100;}
    
        $datostabla[] = array(
         'mes'  => 0,
         'periodo'  => 'T O T A L E S',
         'valor1'  => number_format($totalmtosmes,0,",","."),
         'valor2'  => number_format($totalmtosmes2,0,",","."),
         'dif'  => number_format($dif, 2, ",", ".")." %");
    
              
  $res = [
    "grafico" => $datos,
    "tabla" => $datostabla
  ];
    
} // Fin Accion

    return response()->json($res);

    } // Fin iva_proceso


} // Fin Controlador
