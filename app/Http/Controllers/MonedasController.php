<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\moneda;  // Modelos a utilizar
use App\Models\cotizacion;
use App\Models\precio;
use App\Models\producto;  

use Illuminate\Support\Facades\Auth;  

class MonedasController extends Controller
{

  public function graba_cotizacion(Request $request)
  {
      // Opcion de la - Pantalla Principal para actualizar la Cotizacion de una moneda

      if ( $request->cotiza == 0 or $request->cotiza == '' ) {
            $ret =  " Error debe completar la Cotización " ;
            return  $ret;
      };  

      $row = new cotizacion();
      $row->Cot_FecMov = fechahorahoy();        
      $row->Cot_Moneda = $request->monedaid;         
      $row->Cot_Cotizacion = $request->cotiza;         
      if (Auth::user()) { $row->Cot_UsuAlta=Auth::user()->name; }         
      if ( ! $row->save() ) {
            $ret =  " Error al actualizar tabla Cotización " ;
            return  $ret;
      };  

      //Busco si tiene productos cargados con esa Moneda para Actualziar Precio
      $cantidadProd = 0;
      $datos  = precio::find_moneda( $request->monedaid );
      foreach ($datos as $row) {
         $cantidadProd = $cantidadProd + 1;
         if  ( ! $producto  = Producto::find( $row->idWEB_prod ) ) {
            $ret =  " Error al leer tabla Productos Id:" . $row->idWEB_prod  ;
            return  $ret;
         }
         if ($producto->Prod_Estado != 'I') {
            $producto->Prod_Precio = $row->precio * $request->cotiza;
            $producto->Prod_Precio2 = $row->precio2 * $request->cotiza;
            $producto->Prod_Costo = $row->costo * $request->cotiza;
            $producto->Prod_UsuUltMan = 'ADM_Cotizacion';
            $producto->actualizar() ;
         }   
      } 

      $msg= 'Se actualizo Cotización con Éxito!!   Productos Actualizados: ' .  $cantidadProd;

      return response()->json( [ 'msg' => $msg  ] );
  
  }

  public function index()
  {
      // Lista  - Pantalla Principal
      return view('monedas.index');
  }
 
  public function buscar (Request $request)  {

      if($request->ajax() ) {


        $datos = moneda::buscar($request->filtro_descripcion);

        // Recorro para Completar las cotizaciones
        foreach ($datos as $row) {

            $aux =  cotizacion::mtoEnPesos( $row->mon_moneda ,100,'',$row->cotizacion ,$row->ultfeccot);

        }

        return response()->json([
           'results' => $datos
        ]);

      }  // Fin Ajax

  } // Fin Buscar


  // BOTON  Eliminar (De la tabla principal)
  public function validate_delete(Request $request)
  {

    $registro = Moneda::find($request->id);
    
    // En este caso no hace ninguna validacion de relaciones 
    // $ret_msg = $registro->valida_delete_id($request->id);

    return response()->json([
        "success" => TRUE ,
        "nombre" => $registro->Mon_Descripcion,
        "mensaje" => ""  
    ]);

  }

  public function delete(Request $request)
  {

    // Otras maneras sin utilizar el modelo 
   // $consulta = 'DELETE FROM monedas WHERE  mon_moneda  = ?' ;
   // $num = DB::connection('bdcomercio')->delete($consulta, [$request->id]);


    $item = Moneda::find($request->id);
    $item->delete();


    return response()->json([
      'ret' => 'La moneda '. $request->id ." ha sido borrado de forma exitosa"
    ]);


  }

  public function update(Request $request)
  {


    if  ( ! $moneda  = Moneda::find($request->id) ) {
      abort(402, 'Error: No se encontro el Id:' . $request->id); 
    };

          
    $moneda->fill($request->all());
 
    $moneda->save();
  

    return response()->json([
      'ret' => 'La moneda '. $moneda->Mon_Descripcion  ." ha sido actualizado de forma exitosa"
    ]);

  }



   public function store (Request $request)
  {
     
    //  Inserta el registro en la Tabla

    $reglas = [ 'Mon_Moneda'=>['unique:monedas'],
//               'mon_moneda'=>['max:1'] ,

               'Mon_Descripcion'=>['required'] ,
//               'mon_descripcion'=>['max:40'] ,
               'Mon_CodNum'=>['unique:monedas'] 
    ];

    $attributes =  [
        'Mon_Moneda' => 'Moneda' 
    ];

    $messages = [
        'Mon_Moneda.unique' => 'Ya existe con esa Moneda',
        'Mon_CodNum.unique' => 'Ya existe con ese Código Numérico',
        'Mon_Descripcion.required' => 'Debe completar la Descripción',
    ];
//  Asi valida ok , pero corta si hay error , y el llamado termina con error
//    $this->validate($request,$reglas,$attributes);
    $this->validate($request,$reglas,$messages);

    
    $moneda  = new Moneda($request->all());
    $moneda->Mon_Moneda = $request->Mon_Moneda ; // Hay que pasar el id
    $moneda->Mon_Estado = 'A';
 
    $moneda->save();
        
    return response()->json([
      'ret' => "Se ha registrado ". $moneda->mon_descripcion  ." de manera exitosa ! Id:" . $moneda->mon_moneda
    ]);

  }


  public function show(Request $request)
  {
    // Tiene que estar, lo utiliza para mostrar el index
    // Se utiliza cunado llama a la ventana de Modificar

    $moneda = Moneda::find($request->id);

    return response()->json([
      'id'     => $moneda->Mon_Moneda ,
      'result' => $moneda
    ]);

  }


} // Fin de la Clase
