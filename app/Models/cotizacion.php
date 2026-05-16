<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class cotizacion extends Model {

	protected $primaryKey = 'id';
	protected $table = "cotizacion";

    public static function mtoEnPesos($monedaOri,$montoOri,$fechaCot="",&$valorCot=0, &$ultfecCot='')    {

        $cot_fecmov  = '';
        if ($monedaOri =="P") {
            $mtoPesos = $montoOri ; 
            $valorCot = 1;
            $ultfecCot  = '';            
            return $mtoPesos;
        }    

        if ($monedaOri =="T") {
            $valorCot = 0.9;
            $cot_cotizacion = $valorCot;
        }else{

            if($fechaCot == "") {
                $fechaCot = fechahorahoy (); // Si no cargo fecha tomo cotizacion actual
            }
            $consulta = "SELECT cot_cotizacion , cot_fecmov FROM cotizacion  WHERE    Cot_Moneda='" . $monedaOri 
                     . "' and cot_fecmov<= '" . $fechaCot . "' order by id Desc";
            $datos = DB::select($consulta );         
            if (!$datos ) {
                $cot_cotizacion =1;
            }else{
                $row = $datos[0];
                $cot_cotizacion =$row->cot_cotizacion;
                $cot_fecmov  =$row->cot_fecmov;            
            }
           
        }    

        //  Cambip formula desde 201908   ($monedaOri =="R") {
        //dd (strtotime( $fechaCot) ,strtotime( "11-08-2019" ) );
        if ($monedaOri =="R" and strtotime(  $fechaCot) < strtotime( "11-08-2019" ) ) {
            $mtoPesos = $montoOri / $cot_cotizacion;            
        } else {
            $mtoPesos = $montoOri * $cot_cotizacion;                        
        }
        $valorCot = $cot_cotizacion; // Retorna como parametro por referencia
        $ultfecCot  =$cot_fecmov;            
        return $mtoPesos;

    } //    End Function 

}
