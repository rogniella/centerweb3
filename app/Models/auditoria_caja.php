<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class auditoria_caja extends Model {

	protected $table = "auditoria_caja";
	protected $primaryKey = 'aud_idweb';


    public function save(array $options = array()) {

      	return parent::save($options );
      
  	} // Fin Save


}
