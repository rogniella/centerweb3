<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class tar_liquidacion extends Model
{
    protected $table = 'tar_liquidaciones';

    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

    public static function findIdLiquidacion($id)
    {

        $datos = tar_liquidacion::where('idliquidacion', '=', $id)->first();

        return $datos;

    } // Fin findId

    public static function listar($filtro_producto = '', $filtro_liquidacion = '', $filtro_fecha = '', $filtro_fechafin = '', $filtro_fechaope = '', $filtro_fechafinope = '', $filtro_comercio = '', $limite = 1000)
    {
        $filter = ' where 1=1';
        $valores = [];

        if ($filtro_liquidacion != '') {
            $filter .= ' AND idliquidacion = ?';
            $valores[] = $filtro_liquidacion;
        }

        if ($filtro_producto != '') {
            $filter .= ' AND producto = ?';
            $valores[] = $filtro_producto;
        }

        if ($filtro_comercio != '') {
            $filter .= ' AND comercio = ?';
            $valores[] = $filtro_comercio;
        }

        if ($filtro_fecha != '') {
            $filtro_fecha = $filtro_fecha.' 00:00:00';
            $filtro_fechafin = $filtro_fechafin.' 23:59:59';
            $filter .= ' AND fecha_clearing >= ? and fecha_clearing <= ?';
            $valores[] = $filtro_fecha;
            $valores[] = $filtro_fechafin;
        }

        if ($filtro_fechaope != '') {
            $filtro_fechaope = $filtro_fechaope.' 00:00:00';
            $filtro_fechafinope = $filtro_fechafinope.' 23:59:59';
            $filter .= ' AND fecha_presentacion >= ? and fecha_presentacion <= ?';
            $valores[] = $filtro_fechaope;
            $valores[] = $filtro_fechafinope;
        }

        $limite = is_numeric($limite) && $limite > 0 ? (int) $limite : 1000;

        $consulta = 'SELECT * FROM tar_liquidaciones LEFT JOIN tar_productos ON producto = tar_productos.id '.$filter.' LIMIT '.$limite;

        $ret = DB::select($consulta, $valores);

        return $ret;
    } // Fin Listar

}
