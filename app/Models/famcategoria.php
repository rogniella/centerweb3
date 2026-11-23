<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class famcategoria extends Model
{
    protected $table = 'famcategorias';

    protected $primaryKey = 'FCat_IdCategoria';

    public $timestamps = false;

    protected $fillable = [
        'FCat_IdCategoria',
        'FCat_Familia',
        'FCat_PosIni',
        'FCat_CantPos',
        'FCat_Descripcion',
        'FCat_Glosa',
    ];
}
