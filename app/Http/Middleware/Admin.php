<?php namespace App\Http\Middleware;

/* ran
   Agregarlo en el archivo app/http/kernel,php
*/

use Closure;

use Illuminate\Contracts\Auth\Guard;   // Para manipular las sesiones

class admin {

	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		// Para manipular las sesiones
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	*/

	public function handle($request, Closure $next)
	{
	
		if($this->auth->user()->perfil_id == 'ADM' ) // Si es administrador
		{
			return $next($request); //Continua con el llamado
		}
		else
		{
			abort(401); // Error significa usuario sin permiso  , y tiene una vista para mostar el error
		}
	}

}
