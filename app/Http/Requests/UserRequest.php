<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */

	public function authorize()
	{
		// En la parte se comprobará si el usuario está autorizado a realiza

		return true; //Lo activamos , en False no le deja pasar genera 403  This action is unauthorized.
		
	}

	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		
		// Reglas de validación para los campos
		
		return [
			'name'	=>	'min:2|max:50|required|unique:users',
			'password'	=>	'min:4|max:50|required',
		    'perfil_id'	=>	'required',
		];

	}

	/// *** vr no me esta tomando
	public function attributes()
	{
    	return [
        'name'        => 'Nombre de Usuario',
        'password'    => 'Clave',
		'perfil_id'	=>	'Perfil',
    	];
	}
	
}
