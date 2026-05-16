<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CierreMensual_valor extends Model {

	protected $table = "cierresmensuales_valores";
	public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

	protected $fillable = ['periodo']; 

}
