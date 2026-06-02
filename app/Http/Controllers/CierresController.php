<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\cierre;
use App\Models\mcierre;
use App\Models\mcaja;
use App\Models\sucursal;


class CierresController extends Controller
{

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
      return view('cierres.saldos_cuentas_detalle' , ['sucursales' => $sucursales ] );

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

  public function arqueo()
  {
      $sucursalCodigo = Auth::user()->sucursal;
      $sucursalDescripcion = sucursal::where('codigo', $sucursalCodigo)->value('descripcion');
      $monedasData = $this->calcularMonedas($sucursalCodigo);
      return view('cierres.arqueo', [
          'sucursalCodigo' => $sucursalCodigo,
          'sucursalDescripcion' => $sucursalDescripcion,
          'monedasJson' => json_encode($monedasData),
      ]);
  }

  private function calcularMonedas(string $sucursal): array
  {
      $cuenta = '01'; // Cuenta de Caja General, se puede parametrizar si se quiere cerrar otra cuenta
      $monedas = ['P', 'R', 'D'];
      $resultado = [];

      foreach ($monedas as $moneda) {
          $saldoAnt = 0;
          $ultimoId = 0;
          $fecCierre = '';

          $consulta2 = "SELECT MCie_UltId, MCie_SaldoAnt, created_at FROM mcierre WHERE MCie_Sucursal = ? AND MCie_CodCta = ? AND MCie_Moneda = ? ORDER BY MCie_Id DESC LIMIT 1";
          $results2 = DB::select($consulta2, [$sucursal, $cuenta, $moneda]);
          if ($results2 && $results2[0]) {
              $ultimoId = $results2[0]->MCie_UltId;
              $saldoAnt = $results2[0]->MCie_SaldoAnt;
              $fecCierre = $results2[0]->created_at;
          }

          $totalMov = 0;
          $maxId = $ultimoId;
          $filtro = " WHERE MCaj_idWEB > :ultimoId AND ";
          $filtro .= "((MCaj_sucursal = :sucursal AND MCaj_CtaOri = :cuenta AND MCaj_Moneda = :moneda) ";
          $filtro .= "OR (MCaj_sucursalDes = :sucursal2 AND MCaj_CtaDes = :cuenta2 AND MCaj_MonedaDes = :moneda2)) ";
          $filtro .= " ORDER BY MCaj_idWEB ASC";

          $consulta = "SELECT MCaj_idWEB, MCaj_sucursalDes, MCaj_CtaDes, MCaj_MonedaDes, MCaj_CtaOri, MCaj_Moneda, MCaj_Monto, MCaj_MontoDes, MCaj_Codigo, MCod_HyD FROM mcaja JOIN mcodigo ON MCaj_Codigo = MCod_Codigo " . $filtro;

          $parametros = [
              'sucursal' => $sucursal,
              'cuenta' => $cuenta,
              'moneda' => $moneda,
              'sucursal2' => $sucursal,
              'cuenta2' => $cuenta,
              'moneda2' => $moneda,
              'ultimoId' => $ultimoId,
          ];

          $results = DB::select($consulta, $parametros);
          $saldo = $saldoAnt;

          foreach ($results as $row) {
              if ($row->MCaj_idWEB > $maxId) $maxId = $row->MCaj_idWEB;

              if ($row->MCod_HyD == 'T') {
                  if ($row->MCaj_sucursal == $sucursal && $row->MCaj_CtaOri == $cuenta && $row->MCaj_Moneda == $moneda) {
                      $saldo -= $row->MCaj_Monto;
                  } else {
                      $saldo += $row->MCaj_MontoDes;
                  }
              } elseif ($row->MCod_HyD == 'D') {
                  $saldo -= $row->MCaj_Monto;
              } else {
                  $saldo += $row->MCaj_Monto;
              }
          }

          $totalMov = $saldo - $saldoAnt;

          $resultado[$moneda] = [
              'saldoAnt' => $saldoAnt,
              'totalMov' => $totalMov,
              'saldoEsperado' => $saldo,
              'ultId' => $ultimoId,
              'maxId' => $maxId,
              'tieneMov' => ($ultimoId > 0 || count($results) > 0),
              'fecUltCierre' => $fecCierre ? date('d/m/Y H:i', strtotime($fecCierre)) : '',
          ];
      }

      return $resultado;
  }

  public function arqueoGuardar()
  {
      $sucursal = $_POST['sucursal'];
      $monedasData = json_decode($_POST['monedas'], true);
      $cuenta = '01';
      $usuario = Auth::user()->name;

      // Buscar el último cierre con estado 'I'
      $lastCierre = DB::table('cierre')->where('Cie_sucursal', $sucursal)->where('Cie_estado', 'I')->orderBy('Cie_idWEB', 'desc')->first();

      $cieRetiroP = 0; $cieQuedaP = 0; $cieAjusteP = 0; $cieMotivoP = '';
      $cieRetiroR = 0; $cieQuedaR = 0; $cieAjusteR = 0; $cieMotivoR = '';
      $cieRetiroD = 0; $cieQuedaD = 0; $cieAjusteD = 0; $cieMotivoD = '';

      $idMin = PHP_INT_MAX;
      $idMax = 0;

      $resultadoMonedas = [];

      foreach ($monedasData as $data) {
          $moneda = $data['moneda'];
          $retiro = floatval($data['retiro'] ?? 0);
          $queda = floatval($data['queda'] ?? 0);
          $esperado = floatval($data['saldoEsperado']);
          $ultId = intval($data['ultId']);
          $maxId = intval($data['maxId']);

          // Calcular ajuste = saldoEsperado - (retiro + queda)
          $ajuste = $esperado - ($retiro + $queda);
          $motivo = '';

          if ($ultId > 0) {
              if ($ultId < $idMin) $idMin = $ultId;
          }
          if ($maxId > $idMax) $idMax = $maxId;

          // Insertar nuevo mcierre (consolidación)
          $cierre = new mcierre();
          $cierre->MCie_Moneda = $moneda;
          $cierre->MCie_SaldoAnt = $esperado;
          $cierre->MCie_UltId = $maxId;
          $cierre->MCie_CodCta = $cuenta;
          $cierre->MCie_Sucursal = $sucursal;
          $cierre->MCie_UsuAlta = $usuario;
          $cierre->save();

          // Si hay ajuste, insertar movimiento en mcaja
          if (abs($ajuste) > 0.001) {
              $mov = new mcaja();
              $mov->MCaj_Sucursal = $sucursal;
              $mov->MCaj_SucursalOrig = $sucursal;
              $mov->MCaj_Id = 0;
              $mov->MCaj_SucursalDes = 0;
              $mov->MCaj_FecMov = date('Y-m-d');
              $mov->MCaj_Codigo = $ajuste > 0 ? '0099' : '0991';
              $mov->MCaj_Moneda = $moneda;
              $mov->MCaj_Monto = abs($ajuste);
              $mov->MCaj_CtaOri = $cuenta;
              $mov->MDes_Descripcion = $motivo;
              $mov->MCaj_UsuAlta = $usuario;
              $mov->MCaj_FecAlta = date('Y-m-d H:i:s');
              $mov->save();
          }

          // Asignar a las columnas del cierre según moneda
          switch ($moneda) {
              case 'P':
                  $cieRetiroP = $retiro;
                  $cieQuedaP = $queda;
                  $cieAjusteP = $ajuste;
                  $cieMotivoP = $motivo;
                  break;
              case 'R':
                  $cieRetiroR = $retiro;
                  $cieQuedaR = $queda;
                  $cieAjusteR = $ajuste;
                  $cieMotivoR = $motivo;
                  break;
              case 'D':
                  $cieRetiroD = $retiro;
                  $cieQuedaD = $queda;
                  $cieAjusteD = $ajuste;
                  $cieMotivoD = $motivo;
                  break;
          }

          // Guardar para respuesta
          $resultadoMonedas[$moneda] = [
              'saldoAnt' => floatval($data['saldoAnt'] ?? 0),
              'totalMov' => floatval($data['totalMov'] ?? 0),
              'saldoEsperado' => $esperado,
              'retiro' => $retiro,
              'queda' => $queda,
              'ultId' => $ultId,
              'maxId' => $maxId,
              'tieneMov' => true,
              'fecUltCierre' => $data['fecUltCierre'] ?? '',
          ];
      }

      $now = date('Y-m-d H:i:s');

      if ($lastCierre) {
          // Actualizar el cierre existente
          $cieId = $lastCierre->Cie_idWEB;
          DB::table('cierre')->where('Cie_idWEB', $cieId)->update([
              'Cie_cierre_fecha' => $now,
              'Cie_cierre_usu' => $usuario,
              'Cie_retiro_p' => $cieRetiroP,
              'Cie_queda_p' => $cieQuedaP,
              'Cie_retiro_p_final' => $cieRetiroP,
              'Cie_queda_p_final' => $cieQuedaP,
              'Cie_ajuste_p' => $cieAjusteP,
              'Cie_ajuste_p_motivo' => $cieMotivoP,
              'Cie_retiro_r' => $cieRetiroR,
              'Cie_queda_r' => $cieQuedaR,
              'Cie_retiro_r_final' => $cieRetiroR,
              'Cie_queda_r_final' => $cieQuedaR,
              'Cie_ajuste_r' => $cieAjusteR,
              'Cie_ajuste_r_motivo' => $cieMotivoR,
              'Cie_retiro_d' => $cieRetiroD,
              'Cie_queda_d' => $cieQuedaD,
              'Cie_retiro_d_final' => $cieRetiroD,
              'Cie_queda_d_final' => $cieQuedaD,
              'Cie_ajuste_d' => $cieAjusteD,
              'Cie_ajuste_d_motivo' => $cieMotivoD,
              'Cie_estado' => 'R',
              'Cie_idmov_min' => $idMin < PHP_INT_MAX ? $idMin : 0,
              'Cie_idmov_max' => $idMax
          ]);
      } else {
          // Si no existe cierre 'I', insertar uno nuevo
          $cieId = DB::table('cierre')->insertGetId([
              'Cie_sucursal' => $sucursal,
              'Cie_id' => 0,
              'Cie_cierre_fecha' => $now,
              'Cie_cierre_usu' => $usuario,
              'Cie_retiro_p' => $cieRetiroP,
              'Cie_queda_p' => $cieQuedaP,
              'Cie_retiro_p_final' => $cieRetiroP,
              'Cie_queda_p_final' => $cieQuedaP,
              'Cie_ajuste_p' => $cieAjusteP,
              'Cie_ajuste_p_motivo' => $cieMotivoP,
              'Cie_retiro_r' => $cieRetiroR,
              'Cie_queda_r' => $cieQuedaR,
              'Cie_retiro_r_final' => $cieRetiroR,
              'Cie_queda_r_final' => $cieQuedaR,
              'Cie_ajuste_r' => $cieAjusteR,
              'Cie_ajuste_r_motivo' => $cieMotivoR,
              'Cie_retiro_d' => $cieRetiroD,
              'Cie_queda_d' => $cieQuedaD,
              'Cie_retiro_d_final' => $cieRetiroD,
              'Cie_queda_d_final' => $cieQuedaD,
              'Cie_ajuste_d' => $cieAjusteD,
              'Cie_ajuste_d_motivo' => $cieMotivoD,
              'Cie_estado' => 'R',
              'Cie_idmov_min' => $idMin < PHP_INT_MAX ? $idMin : 0,
              'Cie_idmov_max' => $idMax
          ]);
      }

      return response()->json([
          'success' => true,
          'cie_id' => $cieId,
          'monedas' => $resultadoMonedas,
      ]);
  }

  public function arqueoComprobante()
  {
      $id = $_GET['id'];
      $cierre = DB::table('cierre')->where('Cie_idWEB', $id)->first();

      if (!$cierre) {
          abort(404, 'Cierre no encontrado');
      }

      $sucursal = DB::table('sucursales')->where('codigo', $cierre->Cie_sucursal)->value('descripcion');

      return view('cierres.arqueo_comprobante', [
          'cierre' => $cierre,
          'sucursal' => $sucursal,
      ]);
  }

  public function cierres()
  {
      return view('cierres.index');

  }

  public function listar()
  {
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
          $elem = (array) $row ;
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

      $resp = [
                "success" => TRUE,
                "results" => $datostabla
      ];

      return response()->json($resp);

  }


} // Fin de la Clase
