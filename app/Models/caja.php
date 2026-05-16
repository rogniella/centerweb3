<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)


class caja extends Model
{
    
    protected $table = "caja";
    // Difino Clave Primaria
	protected $primaryKey = 'Caj_IdWEB';
   
    public $timestamps = false;

    public static function buscar($filtro_tipoinforme,$filtro_sucursal,$filtro_fecini ='',$filtro_fecfin ='',$filtro_tipoot =''
             ,$filtro_estado='') {

    	// Se la define static  para llamarla sin objeto con ::	
        // Listado principal, dependiendo de los filtros

        $filter = " where 1=1";
        $valores = [];
        if ($filtro_sucursal != "0") {
            $filter .= " AND Caj_SucursalOri = ?";
            $valores[] = $filtro_sucursal ;
        }
        if ($filtro_tipoinforme != "") {
            $filter .= " AND Caj_Moneda = ?";
            $valores[] = $filtro_tipoinforme ;
        }
        if ($filtro_fecini != "") {
            $filtro_fecini = $filtro_fecini . " 00:00:00";
            $filtro_fecfin = $filtro_fecfin . " 23:59:59";
            $filter .= " AND Caj_FecMov >= '" . $filtro_fecini . "' and Caj_FecMov <= '". $filtro_fecfin . "' ";
        }


        $filter .=  " order by Caj_IdWEB desc";

        $consulta = "SELECT *, DATE_FORMAT(Caj_FecMov , '%d/%m/%Y') as fecha, '' as factura"; 
        $consulta.=" FROM  caja " . $filter; 

        $datos = DB::select($consulta,$valores);
      //  dd($consulta, $datos,$valores);

		return $datos;

    } // Fin Buscar

} //Fin del Modelo
