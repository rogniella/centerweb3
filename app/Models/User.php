<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name', 'email', 'password', 'perfil_id' , 'apellidonombre','id_entidadrelacionada','sucursal', 'home_shortcuts'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'home_shortcuts' => 'array',
    ];

    public function perfil()
    {
        //Recupera la relacion  // Define la relacion  Muchos -> 1
        // Si no se espesifica el 2do parametro asume  perfil_id     nombrerelacion_id
        return $this->belongsTo('App\Perfil','perfil_id'); 
    }

    protected $attributes = [
        // Para definir los valores por defecto
        'email' => 'sin ingresar',
        'apellidonombre' => '',
        'id_entidadrelacionada' => 0
    ];

}
