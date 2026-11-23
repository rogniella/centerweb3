<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class famitemcat extends Model
{
    protected $table = 'famitemcat';

    protected $primaryKey = 'FCatItem_IdCategoria';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'FCatItem_IdCategoria',
        'FCatItem_Codigo',
        'FCatItem_Descripcion',
        'FCatItem_CodPadre',
    ];
}
