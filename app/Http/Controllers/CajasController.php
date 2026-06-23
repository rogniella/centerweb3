<?php

namespace App\Http\Controllers;

use App\Models\cotizacion;
use App\Models\mcaja;
use App\Models\sucursal;  // Para usar SQL directamente (Raw SQL)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajasController extends Controller
{
    public function show(Request $request)
    {

        // Se utiliza cunado llama a la ventana de Modificar para traer los datos por Id

        $ocaja = mcaja::find($request->id);

        $ocaja->MCaj_FecAlta = date('Y-m-d', strtotime($ocaja->MCaj_FecAlta));

        return response()->json([
            'id' => $request->id,
            'result' => $ocaja,
        ]);

    }

    public function store2(Request $request)
    {

        // Boton Aceptar del Alta o Modificacion
        if ($request->operation == 'update') {
            if (! $registro = mcaja::find($request->id)) {
                abort(402, 'Error: No se encontro el Id:'.$request->id);
            }
            $registro->fill($request->all());
            $registro->Mcaj_IdWEB = $request->id; // NO se porque no lo tomaba

        } else { // Alta
            $registro = new mcaja($request->all());
            $registro->MCaj_Origen = '16'; // Mov Detallado Web
            $registro->MCaj_UsuAlta = Auth::user()->name;
            $registro->MCaj_SucursalOrig = env('SUCURSAL_LOCAL');
            $registro->MCaj_SucursalDes = $request->MCaj_Sucursal;

            $registro->MCaj_Id = 0; // Lo uitiliza si se da de alta en las Sucursales
            $registro->MCaj_FecAlta = fechahorahoy();
            $registro->MDes_FecEmision = fechahorahoy();

        }  // Fin Tipo Operacion

        // SI es alta genera el Id , si es modifi, ya genera auditoria en el modelo
        if (! $registro->save()) {
            abort(402, 'Error: Al Actualizar el Id:'.$request->id);
        }

        return response()->json([
            'id' => $registro->MCaj_IdWEB,
            'ret' => 'Se ha registrado de manera exitosa ! ',
        ]);

    }

    public function altas()
    {
        // Pantalla de altas

        // Segun el usuario dejo elegir Sucursal, o solo le dejo la de El
        $sucursales = sucursal::combo(Auth::user()->sucursal);

        // Los Combos lo completa en la pantalla porque ya estaban hechos
        return view('cajas.create', ['sucursales' => $sucursales]);

    }

    public function combo_cuenta_sucursal()
    {
        // Carga las Cuentas Segun la Sucursal  Seleccionada
        $html = '';

        if (isset(request()['sucursal'])) {
            $consulta = "SELECT MCta_CodCta,MCta_Descripcion FROM mcuenta INNER JOIN mcuentasuc ON MCta_CodCta = codcta WHERE sucursal = ? and MCta_Estado<>'I' ORDER BY MCta_CodCta";
            $results = DB::select($consulta, [request()['sucursal']]);
            foreach ($results as $objelem) {
                $row = (array) $objelem;  // Para adaptar a la vs que ya tenia
                if (request()['cod_cuenta'] == $row['MCta_CodCta']) {
                    $html .= '<option value= "'.$row['MCta_CodCta'].'"selected>'.$row['MCta_Descripcion'].'</option>';
                } else {
                    $html .= '<option value= "'.$row['MCta_CodCta'].'">'.$row['MCta_Descripcion'].'</option>';
                }
            }
        }

        $respuesta = ['html' => $html];
        echo json_encode($respuesta);

    } // Fin combo

    public function combo_moneda_cuenta()
    {

        // Carga las Monedas Segun la Cuenta Seleccionada
        $html = '';
        if (isset(request()['cuenta'])) {
            $consulta = 'SELECT MCtaMon_Moneda FROM mctamoneda WHERE MCtaMon_CodCta = ?';
            $results = DB::select($consulta, [request()['cuenta']]);
            foreach ($results as $objelem) {
                $row = (array) $objelem;
                $consulta2 = 'SELECT Mon_Descripcion FROM monedas WHERE Mon_Moneda = ?';
                $ret = DB::select($consulta2, [$row['MCtaMon_Moneda']]);

                if (! $ret) {
                    $descripcion = $row['MCtaMon_Moneda'].' - Error Tabla Moneda';
                } else {
                    $row2 = (array) $ret[0];
                    $descripcion = $row2['Mon_Descripcion'];
                }
                if (request()['moneda'] == $row['MCtaMon_Moneda']) {
                    $html .= '<option value= "'.$row['MCtaMon_Moneda'].'"selected>'.$descripcion.'</option>';
                } else {
                    $html .= '<option value= "'.$row['MCtaMon_Moneda'].'">'.$descripcion.'</option>';
                }
            }
        }

        $respuesta = ['html' => $html];
        echo json_encode($respuesta);

    } // Fin combo_moneda_cuenta

    public function store(Request $request)
    {

        //  Inserta el registro en la Tabla Movimento Detallado

        // Segun Operacion   D  = Movimento Detallado
        //                   T  = Transferencia

        $ocaja = new mcaja;
        $ocaja->MCaj_sucursal = request()['sucursal'];
        $ocaja->MCaj_FecMov = request()['feccaja'];
        if ($request->operacion == 'D') {
            // Movimentos Detallados
            $ocaja->MCaj_Codigo = request()['codmov'];
            $ocaja->MCaj_SucursalDes = request()['sucursal'];
            $ocaja->MCaj_CtaDes = '';
            $ocaja->MCaj_MonedaDes = '';
            $ocaja->MCaj_MontoDes = 0;
        } else {
            // Transferencias
            $ocaja->MCaj_Codigo = '0900'; // Indica que es una transferecia
            $ocaja->MCaj_SucursalDes = request()['sucursalDes'];
            $ocaja->MCaj_CtaDes = request()['cuentaDes'];
            $ocaja->MCaj_MonedaDes = request()['monedaDes'];
            $ocaja->MCaj_MontoDes = request()['montoDes'];
        }

        $ocaja->MCaj_Moneda = request()['moneda'];
        $ocaja->MCaj_Monto = request()['monto'];
        $ocaja->MCaj_CtaOri = request()['cuenta'];
        $ocaja->MDes_Descripcion = request()['nota'];

        $ocaja->MCaj_Origen = '16'; // Mov Detallado Web
        $ocaja->MCaj_UsuAlta = Auth::user()->name;
        $ocaja->MCaj_SucursalOrig = env('SUCURSAL_LOCAL');

        $ocaja->MCaj_Id = 0; // Lo uitiliza si se da de alta en las Sucursales
        $ocaja->MCaj_FecAlta = fechahorahoy();
        $ocaja->MDes_FecEmision = fechahorahoy();
        $ocaja->save();

        return response()->json([
            'ret' => 'Se ha registrado el Movimiento de manera exitosa ! ',
        ]);

    }

    public function transferencias()
    {

        $sucursales = sucursal::combo(Auth::user()->sucursal);

        return view('cajas.transferencias', ['sucursales' => $sucursales]);

    }

    public function ventas()
    {
        // Pantalla Principal de la Consulta
        // return view('cajas.ventas');
        $sucursales = sucursal::combo(Auth::user()->sucursal, 'S');
        $sucursalesModal = sucursal::combo(Auth::user()->sucursal, 'N'); // No permite todas

        return view('cajas.ventas', ['sucursales' => $sucursales, 'sucursalesModal' => $sucursalesModal]);

    }

    public function ventas2()
    {
        // Parte 2 Lista  - Pantalla Principal

        // Tomo parametros de entrada para filtrar
        $sucursal = request()['sucursal'];
        $fecha = request()['fecha'].' 00:00:00';
        $fechafin = request()['fechafin'].' 23:59:59';

        $valores = [];
        $filtro = ' where ';
        if ($sucursal != '0') {
            $filtro = $filtro.' Mcaj_sucursal = ? and';
            $valores[] = $sucursal;
        }

        $filtro = $filtro.' Mcaj_fecMov >= ? and Mcaj_fecMov <= ? and ( Mcaj_CtaOri=\'01\' OR Mcaj_CtaDes=\'01\' ) order by MCaj_Id desc';
        $valores[] = $fecha;
        $valores[] = $fechafin;

        $consulta = "SELECT MCaj_idWEB, MCaj_Codigo, MCaj_sucursal,DATE_FORMAT(MCaj_FecAlta, '%d/%m/%Y') as fecha, ";
        $consulta .= "IF(MCaj_Codigo<>'0900',MCOD_Descripcion,";
        $consulta .= "  IF(Mcaj_CtaOri='93','Cobranza CC',";
        $consulta .= "    IF(Mcaj_CtaOri='94','Cobranza CC Celu',";
        $consulta .= "       IF(Mcaj_CtaOri<>'01','Se Recibio',";
        $consulta .= '           MCOD_Descripcion)))) as descri,';
        $consulta .= " MCaj_Moneda,MCaj_Monto, DATE_FORMAT(MCaj_FecAlta, '%k:%i') as hora, mdes_descripcion,";
        $consulta .= " IF(MCaj_Codigo<>'0900',Mcod_HyD,'T' ) as hyd,mcaj_fecmov, mdes_tipoOT,mdes_idfac  FROM mcaja join mcodigo on  MCaj_Codigo = MCod_Codigo ".$filtro;

        $results = DB::select($consulta, $valores);

        $datostabla = [];

        foreach ($results as $objelem) {

            $elem = (array) $objelem;  // Para adaptar a la vs que ya tenia
            // Si la moneda no es pesos , buscar cotizacion
            $mtoPesos = cotizacion::mtoEnPesos($elem['MCaj_Moneda'], $elem['MCaj_Monto'], $elem['mcaj_fecmov']);
            $datostabla[] = [
                'id' => $elem['MCaj_idWEB'],
                'sucursal' => $elem['MCaj_sucursal'],
                'fecha' => $elem['fecha'],
                'codigo' => $elem['MCaj_Codigo'].'-'.$elem['descri'],
                'monto' => number_format($elem['MCaj_Monto'], env('DEC_MONTO'), ',', '.'),
                'moneda' => $elem['MCaj_Moneda'],
                'mtopesos' => number_format($mtoPesos, env('DEC_MONTO'), ',', '.'),
                'hora' => $elem['hora'],
                'descri' => $elem['mdes_descripcion'],
                'tipoOT' => $elem['mdes_tipoOT'],
                'idfac' => $elem['mdes_idfac'],
                'codH_D' => $elem['hyd'],

            ];

        }

        // Enviar la respuesta Ok.
        $resp = [
            'success' => true,
            'results' => $datostabla,
        ];

        return response()->json($resp);

    } // Fin Ventas2

} // Fin de la Clase
