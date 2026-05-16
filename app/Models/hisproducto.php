<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class hisproducto extends Model {

	protected $primaryKey = 'HisProd_idWEB';
	protected $table = "hisproductos";
	public $timestamps = false;  //no tiene los campos Timestamps "created_at" y "updated_at"

}
