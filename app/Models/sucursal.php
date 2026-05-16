<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sucursal extends Model {

	//

	protected $table = "sucursales";
	protected $primaryKey =  'codigo';
    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"


    public static function combo( $sucursalUsuario = 0, $opcTodas = 'N' ){

      // Carga lista de Sucursales segun el tipo

      //  $sucursalUsuario = 99  No Muestra la Suc On Line 
	  // Segun el usuario dejo elegir Sucursal, o solo le dejo la de El

	  if ($sucursalUsuario == 0 ){  // Todas las Suc
		   if( $opcTodas == 'S' ){
      		 // Si agrega porder elegir todas
             $datos = sucursal::orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo');
           }else{
    	       $datos = sucursal::where('codigo','<>',0)->orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo');
    	   }  
	   }elseif ($sucursalUsuario == 99 ){  // Todas las Suc
			if( $opcTodas == 'S' ){
				// Si agrega porder elegir todas
			  $datos = sucursal::where('codigo','<>',99)->orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo');
			}else{
				$datos = sucursal::where('codigo','<>',99)->where('codigo','<>',0)->orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo');
			}  
	   }else{  // Solo la Suc del Usuario
    	 $datos = sucursal::where('codigo','=', $sucursalUsuario )->orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo');
      	  
      }          
      return $datos;

    } // Fin 

}
