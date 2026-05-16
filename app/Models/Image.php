<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    
    protected $fillable = [
        'url',
    ];

    public function imageable(){
    	// Es un tipo de Relacion que pude tomar muchas formas (Polimorficas),
    	// Es porque se comparte la tabla Imagenes
        return $this->morphTo();  //Transformate
    }

}


