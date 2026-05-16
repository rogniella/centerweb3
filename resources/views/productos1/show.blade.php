@extends('layouts.administracion')

@section('contenido')
<section class="content-header">
    <!-- <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="montserrat"> Países </h1> 
            </div>
        </div>
    </div> -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-header">
                        <h3 class="mb-0 font-weight-bold">Detalles usuario</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th> ID </th>
                                        <td> {{ $item->id }} </td>
                                    </tr>
                                    <tr>
                                        <th> Nombre </th>
                                        <td> {{ $item->name }} </td>
                                    </tr>
                                    <tr>
                                        <th> E-mail </th>
                                        <td> {{ $item->email }} </td>
                                    </tr>
                                    <tr>
                                        <th> Rol </th>
                                        <td>
                                            @foreach($roles as $rol)
                                            {{ $rol }}
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <th> Permisos </th>
                                        <td>
                                            @foreach($permisos as $permiso)
                                            |- {{ $permiso->name }} -|
                                            @endforeach
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a type="button" class="btn btn-danger" href="{{ route('user.index') }}">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection