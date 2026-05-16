<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\proveedor;  // Modelos a utilizar

class ProveedoresController extends Controller
{
     
  public function index()
  {
      // Lista de Proveedores - Pantalla Principal
      return view('proveedores.index');
  }

  public function busca_autocompletar (Request $request)  {

    //  Lo utiliza el auto completar 
    //  Trae 10 items que concuerden con lo ingresado  

    $resbd = proveedor::buscar($_GET["terms"] ,'', 10 );

    // Armo respuesta en un vector para el control del campo auto completar
    $res = [];
    foreach ($resbd as $elem) {
        //tiene que estar por lo que busca en 'name' y 'id' 
        $res[] = [
            'id' => $elem->prov_id,
            'name' => $elem->prov_id . ' - ' . $elem->prov_razsocial
        ];
    }

    return response()->json($res);

  } // Fin busca de auto completar

  public function buscar (Request $request)  {

      // Buscar de la Pantalla Principal
      if($request->ajax() ) {

        $datos = proveedor::buscar($request->filtro_razsocial,$request->filtro_cuit,10000);
 
        return response()->json([ 'results' => $datos ]);

      }  // Fin Ajax

  } // Fin Buscar


  public function validate_cuit_exists (Request $request)  {

        $datos = proveedor::buscar("",$request->cuit,1);
        if (isset( $datos[0] )) {
          return response()->json([
                'existing' => true , 
                'Prov_RazSocial' => $datos[0]->prov_razsocial
          ]);
        }else{          
          return response()->json([
                'existing' =>  false
          ]);
        }  

  } // Fin validate__exists

  // BOTON  Eliminar (De la tabla principal)
  public function validate_delete(Request $request)
  {

    // Valida relaciones que pueda tener antes de Borrarlo
    $registro = proveedor::find($request->id);
    $ret_msg = $registro->valida_delete_id($request->id);

    return response()->json([
        "success" => TRUE ,
        "nombre" => $registro->Prov_RazSocial ,
        "mensaje" => $ret_msg  
    ]);

  }

  // BOTON  Eliminar Confirmado (De la tabla principal)
  public function delete(Request $request)
  {

    $registro = proveedor::find($request->id);
    $registro->delete_id($request->id,$request->id_nvo);

    return response()->json([
      'ret' => 'El proveedor '. $registro->Prov_RazSocial  ." ha sido borrado de forma exitosa"
    ]);

  }

  public function update(Request $request)
  {
    // Boton Modificar de la Pantalla Principal  
    if  ( ! $registro  = proveedor::find($request->id) ) {
      abort(402, 'Error: No se encontro el Id:' . $request->id); 
    };
              
    $registro->fill($request->all());

    $registro->save();

    return response()->json([
      'id' =>  $registro->Prov_id  ,
      'ret' => 'El proveedor '. $registro->Prov_RazSocial  ." ha sido actualizado de forma exitosa"
    ]);

  }

  public function store (Request $request)
  {
     
    //  Inserta el registro en la Tabla
    
      $proveedor = new proveedor($request->all());
 
      $proveedor->save();        

      return response()->json([
        'id' =>  $proveedor->Prov_id  ,
        'ret' => "Se ha registrado de manera exitosa ! Id:" . $proveedor->Prov_RazSocial
      ]);

  }

  public function show(Request $request)
  {
    // Tiene que estar, lo utiliza para mostrar el index
    // Se utiliza cuando llama a la ventana de Modificar para traer los datos por Id 

    $proveedor   = proveedor::find($request->id);

    return response()->json([
      'id' => $proveedor->Prov_id,  
      'result' => $proveedor  
    ]);

  }

} // Fin de la Clase
