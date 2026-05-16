<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\Models\mcaja;  
use App\Models\mcierre;  
use App\Models\cotizacion;
use App\Models\sucursal;  


class CajasController extends Controller
{
    
  public function show(Request $request)
  {

    // Se utiliza cunado llama a la ventana de Modificar para traer los datos por Id 

    $ocaja   = mcaja::find($request->id);

    $ocaja->MCaj_FecAlta = date("Y-m-d", strtotime($ocaja->MCaj_FecAlta));

    return response()->json([
      'id' => $request->id,  
      'result' => $ocaja  
    ]);

  }

  public function store2 (Request $request)
  {

    // Boton Aceptar del Alta o Modificacion   
    if  ( $request->operation == 'update' ) {
      if  ( ! $registro  = mcaja::find($request->id) ) {
        abort(402, 'Error: No se encontro el Id:' . $request->id); 
      };
      $registro->fill($request->all());
      $registro->Mcaj_IdWEB = $request->id; //NO se porque no lo tomaba

    }else{ // Alta
      $registro = new mcaja($request->all());
      $registro->MCaj_Origen="16"; // Mov Detallado Web
      $registro->MCaj_UsuAlta=  Auth::user()->name ; 
      $registro->MCaj_SucursalOrig =  env('SUCURSAL_LOCAL') ;
      $registro->MCaj_SucursalDes =  $request->MCaj_Sucursal ;

      $registro->MCaj_Id = 0; // Lo uitiliza si se da de alta en las Sucursales
      $registro->MCaj_FecAlta=fechahorahoy();
      $registro->MDes_FecEmision=fechahorahoy();

    }  //Fin Tipo Operacion
 
    // SI es alta genera el Id , si es modifi, ya genera auditoria en el modelo     
    if  ( ! $registro->save() ) {
        abort(402, 'Error: Al Actualizar el Id:' . $request->id); 
    };

    return response()->json([
        'id' =>  $registro->MCaj_IdWEB  ,
        'ret' => "Se ha registrado de manera exitosa ! " 
    ]);

  }


  public function cierreCuenta()
  {
      $msgError = "";
      $cierre = new mcierre();
      $cierre->MCie_Moneda=$_GET["moneda"];
      $cierre->MCie_SaldoAnt=$_GET["saldo"];
      $cierre->MCie_UltId=$_GET["ultid"];
      $cierre->MCie_CodCta=$_GET["cuenta"];
      $cierre->MCie_Sucursal=$_GET["sucursal"];
      $cierre->MCie_UsuAlta=  Auth::user()->name ; 
      $cierre->save();
      return response()->json([ 'msgError' => $msgError ]);         

  }

  public function saldosCuentasDetalle()
  {

      $sucursales = sucursal::combo(Auth::user()->sucursal , "N" );
      return view('cajas.saldos_cuentas_detalle' , ['sucursales' => $sucursales ] );

  }


  public function saldosCuentasDetalle2()
  {
      $sucursal=$_GET['sucursal'];
      $cuenta=$_GET['cuenta'];
      $moneda=$_GET['moneda'];

      $saldoAnt = 0;
      $ultimoId = 0;
      $fecCierre ='';    
      // Busco Ultimo Cierre  
      $consulta2  = "SELECT MCie_UltId , MCie_SaldoAnt,  created_at FROM mcierre where MCie_Sucursal =  ? and MCie_CodCta =  ? and MCie_Moneda =  ? ORDER BY MCie_Id Desc limit 1" ;
      $results2 = DB::select($consulta2 , [ $sucursal, $cuenta, $moneda ] );
      if ( $results2 ) {
          if ( $results2[0] ) {
              $ultimoId = $results2[0]->MCie_UltId;
              $saldoAnt = $results2[0]->MCie_SaldoAnt;
              $fecCierre = $results2[0]->created_at;
          }    
      }    


      $filtro= " where MCaj_idWEB > :ultimoId and ";
      $filtro.= "(( Mcaj_sucursal = :sucursal and MCaj_CtaOri= :cuenta and MCaj_Moneda = :moneda ) ";
      $filtro.= "or ( Mcaj_sucursalDes  = :sucursal2 and Mcaj_CtaDes= :cuenta2 and MCaj_MonedaDes = :moneda2 )) ";
      $filtro.= " order by MCaj_idWEB desc";

      $consulta= "SELECT MCaj_idWEB,MCaj_sucursalDes,MCaj_CtaDes, MCaj_MonedaDes, MCaj_CtaOri, MCaj_Origen, MCaj_Codigo, MCaj_sucursal ,DATE_FORMAT(MCaj_FecAlta, '%d/%m/%Y') as fecha, ";  
      $consulta.= "IF(MCaj_Codigo<>'0900',MCOD_Descripcion,";
      $consulta.= "  IF(Mcaj_CtaOri='93','Cobranza CC',";
      $consulta.= "    IF(Mcaj_CtaOri='94','Cobranza CC Celu','falta' ";
      $consulta.= "   ))) as descri,";
      $consulta.= " MCaj_Moneda,MCaj_Monto ,MCaj_MontoDes , DATE_FORMAT(MCaj_FecAlta, '%k:%i') as hora, mdes_descripcion,";
      $consulta.= " IF(MCaj_Codigo<>'0900',Mcod_HyD,'T' ) as hyd,mcaj_fecmov FROM mcaja join mcodigo on  MCaj_Codigo = MCod_Codigo " . $filtro;

      $parametros  = [  'sucursal'  => $sucursal,
                        'cuenta'  => $cuenta,
                        'moneda'  => $moneda,
                        'sucursal2'  => $sucursal,
                        'cuenta2'  => $cuenta,
                        'moneda2'  => $moneda,
                        'ultimoId'  => $ultimoId ];

      $results = DB::select($consulta, $parametros);

//      dd( $consulta, $parametros );  
      $datostabla = [];
      $saldo = $saldoAnt;
                  
      foreach ($results as $row) {

          $debe='';
          $haber='';
          if ($ultimoId < $row->MCaj_idWEB) $ultimoId = $row->MCaj_idWEB;

          if ($row->hyd == 'T') { // Si es una trasferencia
             if ($row->MCaj_sucursal == $sucursal and $row->MCaj_CtaOri == $cuenta and $row->MCaj_Moneda == $moneda ){
               //Origen de la trasferencia
                $debe = number_format($row->MCaj_Monto,env('DEC_MONTO'),",","."); 
                $saldo = $saldo - $row->MCaj_Monto;
                $accion = 'E';
             }else{
               //Destino de la trasferencia
                $haber = number_format($row->MCaj_MontoDes,env('DEC_MONTO'),",","."); 
                $saldo = $saldo + $row->MCaj_MontoDes;
                $accion = 'R';
             }
             $row->descri = $this->descripcionOperacion($row,$accion);
          }else if ($row->hyd == 'D') {
              $debe = number_format($row->MCaj_Monto,env('DEC_MONTO'),",","."); 
              $saldo = $saldo - $row->MCaj_Monto;
          }else{
              $haber = number_format($row->MCaj_Monto,env('DEC_MONTO'),",","."); 
              $saldo = $saldo + $row->MCaj_Monto;
          }

                $elem = (array) $row ;  // Para adaptar a la vs que ya tenia

                $datostabla[] = array(
                 'fecha'  => $row->fecha, 
                 'codigo'  => $row->MCaj_Codigo ."-". $row->descri ,
                 'haber'  => $haber, 
                 'debe'  => $debe, 
                 'moneda'  => $elem["MCaj_Moneda"], 
                 'hora'  => $elem["hora"], 
                 'descri2'  => $saldo ,
                 'descri'  => $elem["mdes_descripcion"] ,
                 'codH_D'  => $elem["hyd"]                  
                );

      }

      // Enviar la respuesta Ok.
      $resp = [
          "success" => TRUE,
          "ultid" =>   $ultimoId   ,
          "saldo" =>   $saldo  ,
          "saldoDescri" =>   $saldo  . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ultimo Cierre: " .    $fecCierre . " &nbsp;&nbsp;Saldo Anterior: $" . $saldoAnt ,
          "results" => $datostabla
      ];

      return response()->json($resp);


  }

  private function descripcionOperacion($row, $accion)
  {

    $descri = $row->descri;  
    if($row->descri == 'falta') {      
      if($accion == 'E') {      
        $descri = 'Se envío a';
        if($row->MCaj_sucursalDes != $row->MCaj_sucursal) $descri .= " Suc:" . $row->MCaj_sucursalDes;
        $descri .= " Cta:". $row->MCaj_CtaDes; 
        if($row->MCaj_MonedaDes != $row->MCaj_Moneda) $descri .= " Moneda:". $row->MCaj_MonedaDes;
      }else{
        $descri = 'Se recibío de';
        if($row->MCaj_sucursalDes != $row->MCaj_sucursal) $descri .= " Suc:" . $row->MCaj_sucursal;
        $descri .= " Cta:". $row->MCaj_CtaOri; 
        if($row->MCaj_MonedaDes != $row->MCaj_Moneda) $descri .= " Moneda:". $row->MCaj_Moneda;
      }
        
    }

    return $descri;  

  }

  public function altas()
  {
      // Pantalla de altas

      // Segun el usuario dejo elegir Sucursal, o solo le dejo la de El
      $sucursales = sucursal::combo(Auth::user()->sucursal);
      // Los Combos lo completa en la pantalla porque ya estaban hechos  
      return view('cajas.create' , ['sucursales' => $sucursales ] );

  }


  public function combo_cuenta_sucursal()
  {
   // Carga las Cuentas Segun la Sucursal  Seleccionada
   $html = "";
      
   if(isset($_GET['sucursal'])){
      $consulta = "SELECT MCta_CodCta,MCta_Descripcion FROM mcuenta INNER JOIN mcuentasuc ON MCta_CodCta = codcta WHERE sucursal = ? and MCta_Estado<>'I' ORDER BY MCta_CodCta";
      $results  = DB::select($consulta , [$_GET['sucursal'] ] );
      foreach ($results as $objelem) {
          $row = (array) $objelem ;  // Para adaptar a la vs que ya tenia
          if ($_GET['cod_cuenta'] == $row["MCta_CodCta"])
              {
                $html .= '<option value= "' . $row["MCta_CodCta"] . '"selected>' .  $row["MCta_Descripcion"] . '</option>';
              }
          else
              {
                $html .= '<option value= "' . $row["MCta_CodCta"] . '">' . $row["MCta_Descripcion"] . '</option>';
              }
      }
    }
  
    $respuesta = array("html"=>$html);
    echo json_encode($respuesta);

  } // Fin combo



  public function combo_moneda_cuenta()
  {
      
   // Carga las Monedas Segun la Cuenta Seleccionada
   $html = "";
   if(isset($_GET['cuenta'])){
      $consulta = "SELECT  MCtaMon_Moneda FROM mctamoneda where MCtaMon_CodCta='" . $_GET['cuenta'] . "'";
      $results  = DB::select($consulta);
      foreach ($results as $objelem) {
          $row = (array) $objelem ;  // Para adaptar a la vs que ya tenia
          $consulta2 = "SELECT Mon_Descripcion FROM monedas  WHERE    Mon_Moneda='".$row["MCtaMon_Moneda"]. "'";
          $ret = DB::select($consulta2);

          if ( !$ret ) {
            $descripcion =  $row["MCtaMon_Moneda"] . " - Error Tabla Moneda";
          }else{
            $row2 = (array) $ret[0];
            $descripcion =  $row2["Mon_Descripcion"];
          }
          if ($_GET['moneda'] == $row["MCtaMon_Moneda"])
              {
                $html .= '<option value= "' . $row["MCtaMon_Moneda"] . '"selected>' .  $descripcion . '</option>';
              }
          else
              {
                $html .= '<option value= "' . $row["MCtaMon_Moneda"] . '">' . $descripcion . '</option>';
              }
      }
    }
  
    $respuesta = array("html"=>$html);
    echo json_encode($respuesta);

  } // Fin combo_moneda_cuenta

  public function store (Request $request)
  {
     
      //  Inserta el registro en la Tabla Movimento Detallado

      // Segun Operacion   D  = Movimento Detallado
      //                   T  = Transferencia

      $ocaja = new mcaja();
      $ocaja->MCaj_sucursal=$_GET["sucursal"];
      $ocaja->MCaj_FecMov=$_GET["feccaja"];
      if ($request->operacion == 'D') {
          //Movimentos Detallados
          $ocaja->MCaj_Codigo=$_GET['codmov'];
          $ocaja->MCaj_SucursalDes =  $_GET["sucursal"] ;
          $ocaja->MCaj_CtaDes="";
          $ocaja->MCaj_MonedaDes='';
          $ocaja->MCaj_MontoDes=0;
      }else{
          //Transferencias
          $ocaja->MCaj_Codigo='0900'; // Indica que es una transferecia
          $ocaja->MCaj_SucursalDes =  $_GET["sucursalDes"] ;
          $ocaja->MCaj_CtaDes= $_GET['cuentaDes'];
          $ocaja->MCaj_MonedaDes=$_GET['monedaDes'];
          $ocaja->MCaj_MontoDes=$_GET['montoDes'];
      }

      $ocaja->MCaj_Moneda=$_GET['moneda'];
      $ocaja->MCaj_Monto=$_GET['monto'];
      $ocaja->MCaj_CtaOri=$_GET['cuenta'];
      $ocaja->MDes_Descripcion=$_GET['nota'];

      $ocaja->MCaj_Origen="16"; // Mov Detallado Web
      $ocaja->MCaj_UsuAlta=  Auth::user()->name ; 
      $ocaja->MCaj_SucursalOrig =  env('SUCURSAL_LOCAL') ;

      $ocaja->MCaj_Id = 0; // Lo uitiliza si se da de alta en las Sucursales
      $ocaja->MCaj_FecAlta=fechahorahoy();
      $ocaja->MDes_FecEmision=fechahorahoy();
      $ocaja->save();
        
      return response()->json([
        'ret' => "Se ha registrado el Movimiento de manera exitosa ! " 
      ]);

  }



  public function cierres()
  {
      // Pantalla de Consulta de Cierres
      return view('cajas.cierres');

  }

  public function cierres2()
  {
      // Parte 2 Boton Listar  

      // Tomo parametros de entrada para filtrar
      $sucursal =$_GET['sucursal'];
      $fecha=$_GET['fecha'] . " 00:00:00";
      $fechafin=$_GET['fechafin'] . " 23:59:59";   

      $filtro = " where ";
      if ($sucursal != "0" ) {
         $filtro = $filtro .  " cie_sucursal = " . $sucursal . " and";     
      }

      $filtro = $filtro . " cie_cierre_fecha >= '". $fecha . "' and cie_cierre_fecha <= '". $fechafin . "'   order by cie_cierre_fecha desc, Cie_Id desc";

      $consulta= "SELECT cie_sucursal,cie_id, DATE_FORMAT(cie_cierre_fecha, '%d/%m/%Y') as fecha ,DATE_FORMAT(cie_cierre_fecha, '%k:%i') as hora , cie_cierre_usu, cie_retiro_p_final , cie_retiro_r_final,cie_retiro_p , cie_retiro_r, cie_ajuste_r,cie_ajuste_r_motivo ,cie_ajuste_p,cie_ajuste_p_motivo, cie_tot_tar ";  
      $consulta.= " FROM cierre " . $filtro;

      $results = DB::select($consulta);
     
      $datostabla = [];
                 
      foreach ($results as $row) {

          $ajuste = "";
          $elem = (array) $row ;  // Para adaptar a la vs que ya tenia
          if ($elem["cie_retiro_p_final"] != $elem["cie_retiro_p"]) {
             $ajuste = "Ver Retiros $ " . number_format($elem["cie_retiro_p_final"],0,".","") . " contra Informado Ini " . number_format($elem["cie_retiro_p"],0,".","") . "<br>";
          }
          if ($elem["cie_retiro_r_final"] != $elem["cie_retiro_r"]) {
             $ajuste = "Ver Retiros R$ " . number_format($elem["cie_retiro_r_final"],0,".","")  . " contra Informado Ini " . number_format($elem["cie_retiro_r"],0,".","")  . "<br>";
          }
          if ( $row->cie_ajuste_p < 0) {
             $ajuste = $ajuste . "Ajuste Perdida $:" . number_format($elem["cie_ajuste_p"],0,".","") . " " . $elem["cie_ajuste_p_motivo"] . "<br>";
          }
          if ( $row->cie_ajuste_p > 0) {
             $ajuste = $ajuste . "Ajuste Falto Anotar $:" . number_format($elem["cie_ajuste_p"],0,".","") . " " . $elem["cie_ajuste_p_motivo"] . "<br>";
          }
          if ( $row->cie_ajuste_r < 0) {
             $ajuste = $ajuste . "Ajuste Perdida R$:" . number_format($elem["cie_ajuste_r"],0,".","") . " " . $elem["cie_ajuste_r_motivo"] . "<br>";
          }
          if ( $row->cie_ajuste_r > 0) {
             $ajuste = $ajuste . "Ajuste Falto Anotar R$:" . number_format($elem["cie_ajuste_r"],0,".","") . " " . $elem["cie_ajuste_r_motivo"] . "<br>";
          }

          $datostabla[] = array(
           'sucursal'  => $elem["cie_sucursal"] ,
           'id'  => $elem["cie_id"] ,
           'fecha'  => $elem["fecha"] ,
           'usuario'  => $elem["cie_cierre_usu"] ,
           'retiro_p'  => number_format($elem["cie_retiro_p_final"],0,".",""), 
           'retiro_r'  => number_format($elem["cie_retiro_r_final"],0,".",""), 
           'tarjeta'  => number_format($elem["cie_tot_tar"],0,".",""), 
           'ajuste'  => number_format($elem["cie_ajuste_p"],0,".",""), 
           'ajuste_motivo'  => $ajuste ,
           'hora'  => $elem["hora"]
          );

      }

      // Enviar la respuesta Ok.
      $resp = [
                "success" => TRUE,
                "results" => $datostabla
      ];

      return response()->json($resp);

  }

  public function transferencias()
  {

      $sucursales = sucursal::combo(Auth::user()->sucursal);
      return view('cajas.transferencias' , ['sucursales' => $sucursales ] );

  }


  public function ventas()
  {
      // Pantalla Principal de la Consulta
      //return view('cajas.ventas');
      $sucursales = sucursal::combo(Auth::user()->sucursal, 'S');
      $sucursalesModal = sucursal::combo(Auth::user()->sucursal, 'N'); //No permite todas
      return view('cajas.ventas' , ['sucursales' => $sucursales ,'sucursalesModal' => $sucursalesModal ] );

  }
 
   public function ventas2()
  {
      // Parte 2 Lista  - Pantalla Principal

      // Tomo parametros de entrada para filtrar
      $sucursal=$_GET['sucursal'];
      $fecha=$_GET['fecha'] . " 00:00:00";
      $fechafin=$_GET['fechafin'] . " 23:59:59";   

      $filtro = " where ";
      if ($sucursal != "0" ) {
         $filtro = $filtro .  " Mcaj_sucursal = " . $sucursal . " and";     
      }

      $filtro = $filtro . " Mcaj_fecMov >= '". $fecha . "' and Mcaj_fecMov <= '". $fechafin . "'     and  ( Mcaj_CtaOri='01' OR Mcaj_CtaDes='01' )  order by MCaj_Id desc";

      $consulta= "SELECT MCaj_idWEB, MCaj_Codigo, MCaj_sucursal,DATE_FORMAT(MCaj_FecAlta, '%d/%m/%Y') as fecha, ";  
      $consulta.= "IF(MCaj_Codigo<>'0900',MCOD_Descripcion,";
      $consulta.= "  IF(Mcaj_CtaOri='93','Cobranza CC',";
      $consulta.= "    IF(Mcaj_CtaOri='94','Cobranza CC Celu',";
      $consulta.= "       IF(Mcaj_CtaOri<>'01','Se Recibio',";
      $consulta.= "           MCOD_Descripcion)))) as descri,";
      $consulta.= " MCaj_Moneda,MCaj_Monto, DATE_FORMAT(MCaj_FecAlta, '%k:%i') as hora, mdes_descripcion,";
      $consulta.= " IF(MCaj_Codigo<>'0900',Mcod_HyD,'T' ) as hyd,mcaj_fecmov, mdes_tipoOT,mdes_idfac  FROM mcaja join mcodigo on  MCaj_Codigo = MCod_Codigo " . $filtro;

      $results = DB::select($consulta);

      $datostabla = [];
                  
      foreach ($results as $objelem) {

                $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
                // Si la moneda no es pesos , buscar cotizacion
                $mtoPesos = cotizacion::mtoEnPesos( $elem["MCaj_Moneda"], $elem["MCaj_Monto"], $elem["mcaj_fecmov"]) ;
                $datostabla[] = array(
                 'id'  => $elem["MCaj_idWEB"], 
                 'sucursal'  => $elem["MCaj_sucursal"], 
                 'fecha'  => $elem["fecha"], 
                 'codigo'  => $elem["MCaj_Codigo"] ."-".$elem["descri"] ,
                 'monto'  => number_format($elem["MCaj_Monto"],env('DEC_MONTO'),",","."), 
                 'moneda'  => $elem["MCaj_Moneda"], 
                 'mtopesos'  => number_format($mtoPesos,env('DEC_MONTO'),",","."),
                 'hora'  => $elem["hora"], 
                 'descri'  => $elem["mdes_descripcion"] ,
                 'tipoOT'  => $elem["mdes_tipoOT"] ,
                 'idfac'  => $elem["mdes_idfac"] ,
                 'codH_D'  => $elem["hyd"] 
                 
                );

      }

      // Enviar la respuesta Ok.
      $resp = [
                "success" => TRUE,
                "results" => $datostabla
      ];

      return response()->json($resp);

  } // Fin Ventas2


} // Fin de la Clase
