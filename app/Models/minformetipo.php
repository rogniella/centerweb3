<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class minformetipo extends Model {


  protected $table = "minformetipo";
  protected $primaryKey =  'infTipo_Id';
  public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

  protected $fillable = ['infTipo_Id', 'infTipo_Descripcion']; // Campos que pueden ser accedidos
  
  
}
