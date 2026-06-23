<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ot_len extends Model
{
    protected $table = 'ot_lc';

    // Dfino Clave Primaria
    protected $primaryKey = 'OtLen_Id';

    public $incrementing = false; // No es auto Incremental

    // Con Access no permite porque es otro tipo de datos
    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

    public static function find_access($id)
    {

        // Se la define static  para llamarla sin objeto con ::
        // Busca por Id

        // No puedo usar la del modelo porque le pone limited
        $datos = Ot_len::where('OtLen_Id', '=', $id)->first();
        
        return $datos;

    } // Fin find

    public function save(array $options = [])
    {

        $this->OtLen_FecUltMan = fechahorahoy();

        return parent::save($options);

    }
} // Fin del Modelo
