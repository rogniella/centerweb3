<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)
use App\Models\minforme;  // Modelos a utilizar
use App\Models\minformetipo;  // Modelos a utilizar

use Illuminate\Support\Facades\Auth;  

class MinformesController extends Controller
{

  public function lee_Tipo2(Request $request)
  {

    $inf = Minforme::find($request->id);
    
    return response()->json([
      'info1'     => $inf->inf_info1 ,
      'info2'     => $inf->inf_info2 
    ]);

  }


  public function index()
  {
      // Lista  - Pantalla Principal
      $tipos = Minformetipo::pluck( 'infTipo_Descripcion','infTipo_Id'); // Para cargar el select
      $informes_tipo1 = Minforme::orderBy('inf_Descripcion', 'ASC')->where( 'inf_tipo','=','1')->pluck( 'inf_Descripcion','inf_Idinforme'); // Para cargar el select
      return view('minformes.index' , ['tipos' => $tipos , 'informes_tipo1' => $informes_tipo1 ] );

  }
 
  public function buscar (Request $request)  {

      if($request->ajax() ) {


        $datos = minforme::buscar($request->filtro_descripcion);

        return response()->json([
           'results' => $datos
        ]);

      }  // Fin Ajax

  } // Fin Buscar


  // BOTON  Eliminar (De la tabla principal)
  public function validate_delete(Request $request)
  {

    $registro = Minforme::find($request->id);
    
    // En este caso no hace ninguna validacion de relaciones 
    // $ret_msg = $registro->valida_delete_id($request->id);

    return response()->json([
        "success" => TRUE ,
        "nombre" => $registro->inf_Descripcion,
        "mensaje" => ""  
    ]);

  }

  public function delete(Request $request)
  {

    $item = Minforme::find($request->id);
    $item->delete();


    return response()->json([
      'ret' => 'El Informe '. $request->id ." ha sido borrado de forma exitosa"
    ]);


  }

  public function update(Request $request)
  {


    if  ( ! $inf  = Minforme::find($request->id) ) {
      abort(402, 'Error: No se encontro el Id:' . $request->id); 
    };

          
    $inf->fill($request->all());
 
    $inf->save();
  

    return response()->json([
      'ret' => 'El Informe '. $inf->inf_Descripcion  ." ha sido actualizado de forma exitosa"
    ]);

  }



   public function store (Request $request)
  {
     
    //  Inserta el registro en la Tabla

    $reglas = [ 
               'inf_Descripcion'=>['required'] 
    ];

    $attributes =  [
        'inf_idInforme' => 'Id Informe' 
    ];

    $messages = [
        'inf_Descripcion.required' => 'Debe completar la Descripción',
    ];
//  Asi valida ok , pero corta si hay error , y el llamado termina con error
//    $this->validate($request,$reglas,$attributes);
    $this->validate($request,$reglas,$messages);

    
    $inf  = new Minforme($request->all());
 
    $inf->save();
        
    return response()->json([
      'ret' => "Se ha registrado ". $inf->inf_Descripcion  ." de manera exitosa ! Id:" . $inf->inf_idInforme
    ]);

  }


  public function show(Request $request)
  {
    // Tiene que estar, lo utiliza para mostrar el index
    // Se utiliza cunado llama a la ventana de Modificar

    $inf = Minforme::find($request->id);

    
    return response()->json([
      'id'     => $request->id ,
      'result' => $inf
    ]);

  }


  public function graba_codigos (Request $request)  {


      $msg = minforme::graba_codigos ( $request->tipoinf , $request->codigos);

      return response()->json([
         'msg' => $msg
      ]);


} // Fin Graba Codigos


public function graba_rendimiento (Request $request)  {


  $msg = minforme::graba_rendimiento ( $request->id , $request->rendimiento);

  return response()->json([
     'msg' => $msg
  ]);


} // Fin Graba Rendimiento



} // Fin de la Clase
