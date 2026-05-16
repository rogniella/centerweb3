<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class comprobantebd extends Model
{
    
    protected $table = "comprobantes";
    // Difino Clave Primaria
	protected $primaryKey = 'Comp_idWEB';

    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"
 

} //Fin del Modelo
