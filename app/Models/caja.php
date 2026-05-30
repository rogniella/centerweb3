<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)


class caja extends Model
{
    
    // Tabla de Formas de Pago
    protected $table = "caja";
    // Defino Clave Primaria
	protected $primaryKey = 'Caj_IdWEB';
   
    public $timestamps = false;

    public static function buscar($filtro_moneda,$filtro_sucursal,$filtro_fecini ='',$filtro_fecfin ='',$filtro_tipoot =''
             ,$filtro_estado='') {

    	// Se la define static  para llamarla sin objeto con ::	
        // Listado principal, dependiendo de los filtros

        $filter = " where 1=1";
        $valores = [];
        if ($filtro_sucursal != "0") {
            $filter .= " AND Caj_SucursalOri = ?";
            $valores[] = $filtro_sucursal ;
        }
        if ($filtro_moneda != "") {
            $filter .= " AND Caj_Moneda = ?";
            $valores[] = $filtro_moneda ;
        }
        if ($filtro_fecini != "") {
            $filter .= " AND Caj_FecMov >= ? AND Caj_FecMov <= ?";
            $valores[] = $filtro_fecini . " 00:00:00";
            $valores[] = $filtro_fecfin . " 23:59:59";
        }

        $filter .=  " order by Caj_IdWEB desc";

        $consulta = "SELECT *, DATE_FORMAT(Caj_FecMov , '%d/%m/%Y') as fecha, '' as factura"; 
        $consulta.=" FROM  caja " . $filter; 

        $datos = DB::select($consulta,$valores);
      //  dd($consulta, $datos,$valores);

		return $datos;

    } // Fin Buscar

} //Fin del Modelo
