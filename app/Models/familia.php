<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class familia extends Model {

	protected $table = "familias";
	protected $primaryKey =  'Flia_Id';
	public $incrementing = false; // No es auto Incremental
	protected $keyType = 'string'; //si es de otro tipo 
    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

}
