<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model {

	//

	protected $table = "perfiles";
	protected $fillable = ['perfil']; 


	public function users(){

		// Define la relacion  1 -> Muchos
		return $this->hasMany('App\User'); 
	}

	public function scopeSearchPerfil($query, $perfil)
	{
		//  Es para ir armando la consulta $query	
		//  El nombre tiene que comenzar con scope  y despues el nombre del metodo comenzando en mayuscula
		
		if ( $perfil != '') {
			return $query->where('perfil', 'LIKE', $perfil);
		} 

	}

}
