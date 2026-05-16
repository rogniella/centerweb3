@extends('template.main')

@section('titulo', 'Nuevo Usuario')

@section('contenido')

<div class="row">
<div class="col-sm-4 col-md-offset-4">
      <div class="modal-content">
        <div class="modal-header">
            <h4> Nuevo Usuario </h4>
        </div>
        <div class="modal-body">

<form action="{{ route('users.store') }}" method="POST">

	<div class="form-group">
		<label for="name">Usuario</label>
		<input type="text" name="name" id="name" class="form-control" placeholder="Nombre Usuario" required>
	</div>

	<div class="form-grup">
		<label for="password">Contraseña</label>
		<input type="password" name="password" class="form-control" placeholder="******" required>
	</div>
	<div class="form-group">
		<label for="apellidonombre">Apellido y Nombre</label>
		<input type="text" name="apellidonombre" id="apellidonombre" class="form-control" placeholder="Apellido y Nombre" required>
	</div>

	<div class="form-group">
		<label for="perfil_id">Perfil</label>
		<select name="perfil_id" id="perfil_id" class="form-control" required>
                        @foreach($perfiles as $key => $value)
                            <option value="{{ $key }}" {{ $key == null ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
	</div>

	<div class="form-group">
		<label for="sucursal">Sucursal</label>
		<select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == 0 ? 'selected' : '' }}>{{ $value }}</option>
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