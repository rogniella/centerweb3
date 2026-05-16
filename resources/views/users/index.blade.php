@extends('template.main')

@section('titulo', 'Lista de usuarios')

@section('contenido')
    
    <!-- Panel de la tabla -->
    <div class="panel panel-info">         
      <div class="panel-heading">
            <h3 class="panel-title">Lista de usuarios</h3>
      </div>
      <div class="panel-body">
		<a href="{{ route('users.create')}}" class="pull-right btn btn-success"><i class="glyphicon glyphicon-plus"></i> Nuevo Usuario</a>
		<table class="table table-striped">
		  <thead>
			<th>Id</th>
			<th>Usuario</th>
			<th>Apellido y Nombre</th>
			<th>Sucursal</th>
			<th>Tipo</th>
			<th>Accion</th>
		  </thead>
		  <tbody>
			@foreach($users as $user)
				<tr>
					<td>{{ $user->id}}</td>
					<td>{{ $user->name}}</td>
					<td>{{ $user->apellidonombre}}</td>
					<td>{{ $user->sucursal_nombre}}</td>
					<td>
						@if($user->perfil_id == 'ADM')
							<span class="label label-danger">{{ $user->perfil_nombre }}</span>
						@else
							<span class="label label-primary">{{ $user->perfil_nombre }}</span>
						@endif
					</td>					
					<td>
						<a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning"> <span class="glyphicon glyphicon-wrench " aria-hidden="true"></span></a> 
						<a href="{{ route('user.mydestroy', $user->id) }}" onclick="return confirm('Seguro de Eliminar Usuario')" class="btn btn-danger"> <span class="glyphicon glyphicon-remove-circle " aria-hidden="true"></span></a>
					</td>
				</tr>
			@endforeach
		  </tbody>
		</table>
       </div> <!-- Fin Panel Body -->
    </div> <!-- Fin Panel Info -->

	{{--  Es para mostrar la paginacion de la vista --}}
	{!! $users->render() !!}

@endsection()