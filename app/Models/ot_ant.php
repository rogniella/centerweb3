<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ot_ant extends Model
{
    
    protected $table = "ot_ant";
 	protected $primaryKey = 'OtAnt_IdWEB';

    public static function find_suc_id ( $sucursal, $id ){

        // Se la define static  para llamarla sin objeto con :: 

        $datos = Ot_Ant::where('OtAnt_Sucursal', '=', $sucursal)->where('OtAnt_Id', '=', $id)->first();
     
        return $datos;

    } // Fin find


} //Fin del Modelo
