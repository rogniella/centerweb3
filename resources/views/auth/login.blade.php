@extends('template.login')
@section('titulo','Incio de Sesión')

@section('contenido')

<form method="POST" action="{{ route('login') }}">
     @csrf
<div class="container"> 
    <div class="col-sm-4">
        <h1 class="title-agile text-center">{{ config('app.name', 'Laravel') }}</h1>
        <div class="panel panel-info"> 
            <div class="panel-heading">
                <h3 class="panel-title">Iniciar sesion</h3>
            </div>
        <div class="panel-body">
            <div class="field_w3ls">
                <div class="field-group">
                  <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required autocomplete="name" autofocus>

                    <div style="color:WHITE;"> .</div> <!-- Separacion  -->
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                </div>
            </div>
            <div class="field-group">
                <div style="color:WHITE;"> .</div> <!-- Separacion  -->
                <div id="msgErrModal"  style="display:none;" class="alert alert-danger" align="left" >completa por programa</div>
                <div style="color:WHITE;"> .</div> <!-- Separacion  -->
                <button type="submit" class="btn btn-success btn-block">Ingresar</button>
            </div>
        </div>     <!--Fin Body -->
    </div>
</div>
</form>

@endsection
