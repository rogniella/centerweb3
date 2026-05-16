<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)
use App\Models\cliente;  // Modelos a utilizar


class ClientesController extends Controller
{
    
  public function index()
  {
      // Lista de Clientes - Pantalla Principal
      return view('clientes.index');
  }

  public function buscar (Request $request)  {

    // Buscar de la Pantalla Principal
    if($request->ajax() ) {

      $datos = cliente::buscar($request->filtro_apenom,$request->filtro_documento,100000);

      return response()->json([ 'results' => $datos ]);

    }  // Fin Ajax

  } // Fin Buscar

  public function busca_autocompletar (Request $request)  {

    //  Lo utiliza el auto completar 
    //  Trae 20 items que concuerden con lo ingresado  

    $resbd = cliente::buscar($_GET["terms"] ,'', 20 );

    // Armo respuesta en un vector para el control del campo auto completar
    $res = [];
    foreach ($resbd as $elem) {
        //tiene que estar por lo que busca en 'name' y 'id' 
        $res[] = [
            'id' => $elem->cli_idWEB,
            'idSUC' => $elem->cli_id,
            'name' => $elem->cli_documento . ' - ' . $elem->cli_apenom . ' (' . $elem->cli_id . ')',
            'apenom' =>  $elem->cli_apenom,
            'documento' => $elem->cli_documento,
            'coddocumento' => $elem->cli_coddocumento,
            'telefono' => $elem->cli_telefono
        ];
    }

    return response()->json($res);

  } // Fin busca de auto completar

  public function TemporalArreglo1buscar (Request $request)  {

        // temporal para arreglar los RI , el Nro de Documento / cuit

        $resbd = cliente::buscarRI();
     // Armo respuesta en un vector para el control del campo auto completar
    $datos = [];
    foreach ($resbd as $elem) {

        if( $elem->cli_cuil == '00000000000' or $elem->cli_cuil == '0' or $elem->cli_cuil == '') {
          $elem->cli_coddocumento =  $elem->cli_coddocumento  .  '- err paso a CF';
            $query_operacion = "UPDATE clientes SET  Cli_CodRespIVA = 'CF' WHERE   cli_Id= $elem->cli_id";
            $resultado = DB::update($query_operacion);           
          // lo paso a CF
        }else{
          if ($elem->cli_cuil != $elem->cli_documento ) {
             if( strlen ($elem->cli_cuil) == 11 ) {
                $elem->cli_coddocumento =  $elem->cli_coddocumento  .   '-arreglar';
                // Arreglo cod documento y nro
              $query_operacion = "UPDATE clientes SET  cli_coddocumento = 'CUIT', cli_documento = '$elem->cli_cuil' WHERE   cli_Id= $elem->cli_id";
              $resultado = DB::update($query_operacion);  

            }else{
                $elem->cli_coddocumento =  $elem->cli_coddocumento  .   '-ErrorCUIT';               
                // lo paso a CF
              $query_operacion = "UPDATE clientes SET  Cli_CodRespIVA = 'CF' WHERE   cli_Id= $elem->cli_id";
              $resultado = DB::update($query_operacion);  
             }   
          }            
        }


        $datos[] = [
            'cli_id' => $elem->cli_id,
            '33' => $elem->cli_documento . ' - ' . $elem->cli_apenom,
            'cli_apenom' =>  $elem->cli_apenom,
            'cli_documento' => $elem->cli_documento,
            'cli_coddocumento' => $elem->cli_coddocumento,
            'cli_telefono' => $elem->cli_cuil
        ];
    }

        return response()->json([ 'results' => $datos ]);

  } // Fin Buscar


  public function TemporalArreglobuscar (Request $request)  {

        // temporal para arreglar los tipo de docuemtos  Nro de Documento / cuit

        $resbd = cliente::buscarRI();
     // Armo respuesta en un vector para el control del campo auto completar
    $datos = [];
    foreach ($resbd as $elem) {


      if( strlen ($elem->cli_documento) != 11 ) {
                $elem->cli_coddocumento =  $elem->cli_coddocumento  .   '-arreglar';
                // Arreglo cod documento y nro
             // $query_operacion = "UPDATE clientes SET  cli_coddocumento = 'CUIT', cli_documento = '$elem->cli_cuil' WHERE   cli_Id= $elem->cli_id";
             // $resultado = DB::connection('bdcomercio')->update($query_operacion);  

        $datos[] = [
            'cli_id' => $elem->cli_id,
            '33' => $elem->cli_documento . ' - ' . $elem->cli_apenom,
            'cli_apenom' =>  $elem->cli_apenom,
            'cli_documento' => $elem->cli_documento,
            'cli_coddocumento' => $elem->cli_coddocumento,
            'cli_telefono' => $elem->cli_cuil
        ];

      }            
  


    }

        return response()->json([ 'results' => $datos ]);

  } // Fin Buscar




  public function validate_dni_exists (Request $request)  {

        $datos = cliente::buscar($request->dni,'',1);
        if (isset( $datos[0] )) {
          return response()->json([
                'existing' => true , 
                'Cli_ApeNom' => $datos[0]->cli_apenom ,
                'Cli_CodRespIVA' => $datos[0]->cli_codrespiva ,
                'Cli_Calle' => $datos[0]->cli_calle ,
                'Cli_Telefono' => $datos[0]->cli_telefono ,
                'Cli_idWEB' => $datos[0]->cli_idWEB ,
                'Cli_Id' => $datos[0]->cli_id ,
          ]);
        }else{
          return response()->json([
                'existing' =>  false  
          ]);
        }  

  } // Fin validate_dni_exists


  // BOTON  Eliminar (De la tabla principal)
  public function validate_delete(Request $request)
  {

    $registro = Cliente::find_idWEB($request->id);
    $ret_msg = $registro->valida_delete_id($registro->Cli_Id);

    return response()->json([
        "success" => TRUE ,
        "nombre" => $registro->Cli_ApeNom ,
        "mensaje" => $ret_msg  
    ]);

  }

  // BOTON  Eliminar Confirmado (De la tabla principal)
  public function delete(Request $request)
  {

    $id_nvo = 0;  
    $registro = Cliente::find_idWEB($request->id);
    if (isset( $request->id_nvo )) {
     if ($request->id_nvo > 0  ) {
      $cliente_nvo = Cliente::find_idWEB($request->id_nvo);
      $id_nvo = $cliente_nvo->Cli_Id;
      if ( $cliente_nvo->Cli_Sucursal !=  $registro->Cli_Sucursal  ) {
        return "No se permite reasignar con Cliente de Otra Sucursal"; //Retorna Error
      }
     }
    }

    $registro->delete_id($registro->Cli_Id, $id_nvo);

    return response()->json([
      'ret' => 'El Cliente '. $registro->Cli_ApeNom  ." ha sido borrado de forma exitosa"
    ]);

  }

  public function update(Request $request)
  {
    // Boton Modificar de la Pantalla Principal  
    if  ( ! $registro  = Cliente::find_idWEB($request->id) ) {
      abort(402, 'Error: No se encontro el IdWEB:' . $request->id); 
    };
              
    $registro->fill($request->all());
    $registro->Cli_IdWEB = $request->id; 
    $registro->Cli_FecUltMan = fechahorahoy(); 

    if ( ! $registro->save() ) {
       dd("Error: xxxx");
    }

    return response()->json([
      'id' =>  $registro->Cli_idWEB  ,
      'ret' => 'El Cliente '. $registro->Cli_ApeNom  ." ha sido actualizado de forma exitosa"
    ]);

  }

  public function store (Request $request)
  {
     
    //  Inserta el registro en la Tabla
    
      $cliente = new Cliente($request->all());
      $cliente->Cli_Id = 0;  // Id en Sucursal, despues le dejamos el mismo para los casos Web

      $cliente->Cli_FecAlta = fechahorahoy();    
 
      $cliente->save();  
      // En estos casos que son Clientes dados de alta en la Web, tomo Id de Sucursal el mismo
      $cliente->Cli_Id = $cliente->Cli_idWEB;
      $cliente->save();  

  //   dd($cliente);


      return response()->json([
        'id' =>  $cliente->Cli_idWEB ,
        'ret' => "Se ha registrado de manera exitosa ! Id:" . $cliente->Cli_idWEB
      ]);

  }


  public function show(Request $request)
  {
    // Tiene que estar, lo utiliza para mostrar el index
    // Se utiliza cunado llama a la ventana de Modificar para traer los datos por Id 

    $cliente   = Cliente::find_idWEB($request->id);

    return response()->json([
      'id' => $cliente->Cli_idWEB,  
      'result' => $cliente  
    ]);

  }

  public function consulta(Request $request)
  {


   //  http://localhost/centerweb2/public/clientes/consulta?id=1000005

    // Lo busca por Id de Sucursal
    $cliente   = Cliente::find_id($request->id);
    if ($cliente == null ) {
      $cliente   = Cliente::find($request->id); //Por id WEB
    }
  //  $cliente->ots; //Para que cargue las Ot Relacionadas
  //  $cliente->ventas; //Para que cargue las Ot Relacionadas

   //  dd($cliente  );
    if($request->ajax() ) {
      return view('clientes.consulta' , ['cliente' => $cliente ]);   
    }else{ 
      return view('clientes.page_consulta' , ['cliente' => $cliente ]);
    }
  }

  public function informecc()
  {

    // http://localhost/gestion/public/clientes/informecc
    
    // Si no se le pasa el nro de cta, tomo del login si es cliente, el conectado
    $cta = 0;
    $apenom = '';
    if(Auth::user() and  Auth::user()->perfil_id == 'CLI' and  Auth::user()->id_entidadrelacionada > 0  ){
      $cta = Auth::user()->id_entidadrelacionada;
    }else{
       if ($_GET) {
        $cta = $_GET["cta"];
       }    
    }  

    if( $cta > 0  ) {
      $registro = Cliente::find($cta);
      $apenom = $registro->Cli_ApeNom;
    }

    return view ('clientes.informecc', ['cta' => $cta , 'apenom' => $apenom ] );

  }


  public function informecc_proceso()
  {

  //  Boton de proceso por ajax del Informe Cuenta Corriente 
  //  Tomo parametros de entrada para filtrar
  $tipo_informe=$_GET['tipo_inf'];

  $cta=$_GET['cta'];
  $fecha=$_GET['fecha'];
  $fechafin=$_GET['fechafin'];
  
  // En MDes_IdProv  esta el id del cliente con CC con nro negativo
  $cta = $cta * -1;
  $filtro = " where MDes_IdProv = " . $cta;
  
  // Segun el tipo de informe
    switch ($tipo_informe) {
        case 'P':  // Pendientes
            $filtro = $filtro . " and ( MDes_Estado = 'D' or MDes_Estado = 'F' or MDes_Estado = 'Z' )  and ( OriTipo = 'CL') and MCaj_Codigo <> '0900'";
            $fecha='0000-00-00';
            $fechafin='9999-99-99';
            break;
        case 'T':  // Todos
            $filtro = $filtro . " and OriTipo = 'CL' or DesTipo = 'CL' ";
            break;            
    }

    $consulta = "SELECT Format(MDes_FecEmision, 'yyyy-mm-dd') as fecemi ,mdes_descripcion,MCaj_Monto , Format(MCaj_FecMov, 'yyyy-mm-dd') as fecmov,MDes_Estado,MCaj_Id,mcod_descripcion,Mcaj_Codigo, Mdes_NroComprobante  FROM McajaConDescri ". $filtro . " order by MCaj_FecMov , Mcaj_id";
//    $consulta = "SELECT Format(MDes_FecEmision, 'yyyy-mm-dd') as fecemi ,mdes_descripcion,MCaj_Monto , Format(MCaj_FecMov, 'yyyy-mm-dd') as fecmov,MDes_Estado,MCaj_Id,mcod_descripcion,Mcaj_Codigo, Mdes_NroComprobante  FROM McajaConDescri ". $filtro . " order by MDes_FecEmision , Mcaj_id";

    $resbd = DB::select($consulta ); 


    $datostabla = [];
    $saldo = 0;
            
    foreach ($resbd as $objelem) {
        $elem = (array) $objelem ;  // Para adaptar a la vs que ya tenia
         $comprobante = $elem["mdes_descripcion"] ; //   . " // " . $elem["mcod_descripcion"];  
         $estado = 'Pendiente';
         $haber = 0;
         $debe = $elem["MCaj_Monto"] ;
         switch ($elem["MDes_Estado"]) { // F=Tiene pago parcial    X=Todo Pago   D=Debe  Z= Anulado
           case 'F':  
             $estado = 'Cobro Parc';
             // Si el tipo de Informe es Pendientes , busco si tiene pago
             if($tipo_informe == 'P') {  
                $consultapago =  "SELECT sum(MPag_Monto) as pago FROM MPagos  WHERE    MPag_IdCompra=" . $elem["MCaj_Id"] ;
                $row = DB::select($consultapago ); 
                $haber = $row[0]->pago; 
             }   
             break;
           case 'Z':  
             $estado = 'Anulado';
             $debe = 0 ;
             break;         
           case 'X':  
             $estado = 'Pagado';
             break;         
         }
         
         // Si es un pago
         if( $elem["Mcaj_Codigo"] == '0900') {
              $comprobante =    "Recibo:" . $elem["Mdes_NroComprobante"] . " " . $elem["mdes_descripcion"];
              $debe = 0;
              $haber = $elem["MCaj_Monto"] ;
              $estado = '';
         }
         $saldo = $saldo + $debe - $haber ;
         if ( $elem["fecmov"] >= $fecha and $elem["fecmov"] <= $fechafin) {
          $datostabla[] = array(
            'fecha'  => $elem["fecmov"] ,
            'comprobante'  => $comprobante, 
            'estado'  => $estado, 
            'vencimiento'  => $elem["fecemi"] ,
            'debe'  => number_format($debe,0,"","."),
            'haber'  => number_format($haber, 0,"","."),
            'saldo'  =>  number_format($saldo,0,"",".")
          );    
         }
    } // Fin Foreach

  // Enviar la respuesta Ok.    
  $res = [
            "success" => TRUE,
            "saldo" =>  number_format($saldo,0,"","."),
            "results" => $datostabla
  ];

    return response()->json($res);


  }  //  Fin informecc_proceso

} // Fin de la Clase
