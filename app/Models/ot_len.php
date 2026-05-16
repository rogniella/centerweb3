<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ot_len extends Model
{
    

    protected $table = "ot_lc";
    // Difino Clave Primaria
		protected $primaryKey = 'OtLen_Id';
    	public $incrementing = false; // No es auto Incremental
    // Con Access no permite porque es otro tipo de datos
    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"


    public static function find_access(  $id ){

        // Se la define static  para llamarla sin objeto con :: 
        // Busca por Id

        // No puedo usar la del modelo porque le pone limited    
        if ( $datos = Ot_len::where('OtLen_Id', '=', $id)->first() ) {
            $datos->attributes = convert_from_latin1_to_utf8_recursively($datos->attributes);
            $datos->original = convert_from_latin1_to_utf8_recursively($datos->original);
        }
     
        return $datos;

    } // Fin find

    public function save(array $options = array()) {

        $this->OtLen_FecUltMan = fechahorahoy();               
	    return parent::save($options );

    }

} //Fin del Modelo
