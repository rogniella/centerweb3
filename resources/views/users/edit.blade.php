@extends('template.main')

@section('titulo', 'Editar Usuario '. $user->name)

@section('contenido')

<div class="row">
<div class="col-sm-4 col-md-offset-4">
      <div class="modal-content">
        <div class="modal-header">
            <h4> Editar Usuario </h4>
        </div>
        <div class="modal-body">

{!! Form::open(['route' => ['users.update', $user], 'method' => 'PUT']) !!}

	<div class="form-group">
		<label for="name">Usuario</label>
		<input type="text" name="name" id="name" class="form-control" placeholder="Nombre Completo" value="{{ $user->name }}" required>
	</div>

	<div class="form-group">
		<label for="apellidonombre">Apellido y Nombre</label>
		<input type="text" name="apellidonombre" id="apellidonombre" class="form-control" placeholder="Apellido y Nombre" value="{{ $user->apellidonombre }}" required>
	</div>

	<div class="form-group">
		<label for="perfil_id">Perfil</label>
		<select name="perfil_id" id="perfil_id" class="form-control" required>
                        @foreach($perfiles as $key => $value)
                            <option value="{{ $key }}" {{ $key == $user->perfil_id ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
	</div>

	<div class="form-group">
		<label for="sucursal">Sucursal</label>
		<select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $user->sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
	</div>

   </div> <!-- FIN Modal body -->
   <div class="modal-footer">
		<button type="submit" class="btn btn-primary">Aceptar</button>
        <button type="button" class="btn btn-default" data-dismiss="modal" onClick=" window.history.back()">Cancelar</button>
   </div>

</form>
 </div>
</div>
</div>
</div>

@endsection