<?php

namespace App\Http\Middleware;

/* ran
   Agregarlo en el archivo app/http/kernel,php
*/

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

   // Para manipular las sesiones

class admin
{
    protected $auth;

    /**
     * Create a new filter instance.
     *
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
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if ($this->auth->user()->perfil_id == 'ADM') { // Si es administrador
            return $next($request); // Continua con el llamado
        } else {
            abort(401); // Error significa usuario sin permiso  , y tiene una vista para mostar el error
        }
    }
}
