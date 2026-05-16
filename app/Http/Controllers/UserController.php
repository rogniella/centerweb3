<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Perfil;  // Modelo Relacionado
use App\Models\sucursal;  // Modelo Relacionado


use App\Http\Requests\UserRequest;  // Para validar Formulario

use Hash;  // Para validar la clave en la pantalla de cambio
use Illuminate\Support\Facades\Auth;
use Laracasts\Flash\Flash;



class UserController extends Controller {


 	public function AuthRouteAPI(Request $request){
  	 	 return $request->user();
 	}

	public function index()
	{

		// Lista los usuarios - Pantalla Principal

		// 1)-  Asi hace bien en solo 1 instruccion , pero no se puede user la funcion paginate()	
		//  $consulta= "SELECT * FROM users join perfiles on users.perfil_id=perfiles.id " ;
        //  $users = \DB::select($consulta);


		//	2)- Asi ada bien todo, pero muchos select, 1 por cada relacion ya	 registro 
		  	//   $users = User::orderBy('id', 'ASC')->paginate(10);	
   			// No es necesario , al estar relacionadas ya hace
			//	$users->each(function($users){
			//		$users->perfil;  //Recupera las relaciones , hace un select a la tabla relacionada por c/registro
			//	});

		// 3)- Hace un join , puede paginar	, pero tiene el problema que si hay campos con el mismo nombre deja el relacionado (ej id), por lo que hay que espesificar el select
  	try {

  		$users = User::join('perfiles', 'users.perfil_id', '=', 'perfiles.id')
  		      ->join('sucursales', 'users.sucursal', '=', 'sucursales.codigo')
  		      ->select( 'users.id','name','apellidonombre', 'sucursales.descripcion as sucursal_nombre',
  		      	'perfil_id','perfiles.nombre as perfil_nombre') 
  			  ->orderBy('users.id', 'ASC')->paginate(50);


	} catch(Illuminate\Database\QueryException $e) {
		dd ("capturo el try", $e); 
 		return Redirect::to('users')->with('status', 'error_create');
	} 

	// En controlado del que hace la pantalla
		$users2 =   [
    			(object) [  'id' => '1',
    			'name' => 'nombre pru 1',
    			'apellidonombre' => 'apelli pru 1',
    			'sucursal_nombre' => 'sucqqq pru 1',
    			'perfil_id' => 'ADM',
    			'perfil_nombre' => 'ADMINIS'],

    			(object) [ 'id' => '2',
    			'name' => 'nombre pru 2',
    			'apellidonombre' => 'apelli pru 2',
    			'sucursal_nombre' => 'suc pru 2',
    			'perfil_id' => 'CLI',
    			'perfil_nombre' => 'Cliente'],

    			(object) [ 'id' => '2',
    			'name' => 'nombre pru Nuevas',
    			'apellidonombre' => 'apelli pxxxx',
    			'sucursal_nombre' => 'suc pru 2',
    			'perfil_id' => 'CLI',
    			'perfil_nombre' => 'Cliente']

  			];

  	//	dd ($users,$users2);	


		return view('users.index')->with('users', $users );		

	}


	public function create()
	{
		//  LLamar a la Pantalla de Alta
        $perfiles = Perfil::orderBy('nombre', 'ASC')->pluck( 'nombre','id'); // Para cargar el select
        $sucursales = sucursal::orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo'); // Para cargar 
		return view('users.create')->with('perfiles', $perfiles)->with('sucursales', $sucursales);
	}


	public function store(UserRequest $request)
	{
	
		//  Inserta el registro en la Tabla
		//  La validacion esta en  /app/http/requests/UserRequest
		
		$user = new User($request->all());
		$user->password = bcrypt($request->password); // Para encriptar contraseña
		$user->save();

	 	Flash::success("Se ha registrado ". $user->name ." de manera exitosa ! Id:" . $user->id );

		return redirect()->route('users.index');

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		// Tiene que estar, lo utiliza para mostrar el index

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		// LLamar a la ventana de Modificar
		$user = User::find($id);

        $perfiles = Perfil::orderBy('nombre', 'ASC')->pluck( 'nombre','id'); // Para cargar el select
        $sucursales = sucursal::orderBy('codigo', 'ASC')->pluck( 'descripcion','codigo'); // Para cargar el select
//		mando de la otra forma para mandar mas de un parametro
        //return view('users.edit')->with('user', $user , 'perfiles', $perfiles);
		return view('users.edit' , [ 'user' => $user , 'perfiles' => $perfiles , 'sucursales' => $sucursales ] );
	}

	/**
	* Update Boton Aceptar de Ventana de Modificacion.
	*/
	public function update(Request $request, $id)
	{

		// Acepta la Modificacion
		
		$this->validate($request, [
        	'name' => 'required|max:10',
        	'perfil_id' => 'required',
        	// 'precio' => 'required|numeric',
	        // 'name'=>['required',new \App\Rules\Cbu()],
            // 'name'=>['required',new \App\Rules\Cuit()], 
    	]);

		$user = User::find($id);

		$user->fill($request->all());
		/*  Se puede usar el metodo fill para pasar todos los valores de una
		$user->name = $request->name;
		$user->tipo = $request->tipo;
		*/
		$user->save();

		Flash::success('El usuario '. $user->name .' ha sido editado con exito');
		return redirect()->route('users.index');

	}

	/**
	 * BOTON Eliminar
	 */
	public function destroy($id)
	{
		$user = User::find($id);
		//dd( "Entro a eliminar" , $id );
		$user->delete();
		Flash::error('El usuario '. $user->name ." ha sido borrado de forma exitosa");
		return redirect()->route('users.index');
	}

    public function password(){
        return View('users.password');
    }

	public function updatePassword(Request $request){

        $reglas = [
            'mypassword' => 'required',
            'password' => 'required|confirmed|min:4|max:18',
        ];
        $messages = [
            'mypassword.required' => 'La Contaseña Actual es requerida',
            'password.required' => 'El campo es requerido',
            'password.confirmed' => 'Las Contraseñas Nuevas no coinciden',
            'password.min' => 'El mínimo permitido son 4 caracteres',
            'password.max' => 'El máximo permitido son 18 caracteres',
        ];
 	    $this->validate($request,$reglas,$messages);
       
 	    // Comprueba la contraseña actual
        if (Hash::check($request->mypassword, Auth::user()->password)){
            $user = new User;
            $user->where('name', '=', Auth::user()->name)
                 ->update(['password' => bcrypt($request->password)]);
      //** ver no sale msg de confirmacion **
			Flash::success('El usuario '. $user->name .' Cambio Contraseña con éxito');
			return redirect()->route('home');
        } else {
			Flash::error('Credenciales incorrectas');
			return redirect('user/password');
        }

    } //Fin Update Password

} // Fin de Controller