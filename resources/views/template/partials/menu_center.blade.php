<!-- MENU Principal de la Aplicacion -->
<!-- Barra Superiro Fija de Navegacion Fixed navbar -->
<nav class="navbar navbar-inverse">
<div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{ route('home') }}"><strong>{{ config('app.name', 'Optica') }}</strong></a>
    </div>     <!-- Fin Header -->

    <!-- Si no esta Conectado Authentication Links -->
    @guest
      <div class="navbar-header pull-right navbar-right">
         <a class="navbar-brand" href="{{ route('login') }}"><i class="glyphicon glyphicon-log-in"></i><strong> Ingreso</strong></a>
      </div>

    @else

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div id="navbar" class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        @if( env('MODULO_OPTICA') == "N" ) 
          <!-- Menu principal -->
          @include('template.partials.menu_gestion')
        @else
          @include('template.partials.menu_optica')
        @endif  <!-- CUIT -->
    
        <ul class="nav navbar-nav navbar-right">
        <li><a href="#"<b>Suc: {{ Auth::user()->sucursal }}</b></a></li>
        <li class="dropdown">          
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <span class="glyphicon glyphicon-user"></span> {{ Auth::user()->name }}      <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="{{url('user/password')}}">Cambiar Contraseña</a></li>

              <li><a href="{{ route('logout')}}" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
              </li>
            
              <li role="separator" class="divider"></li>
        </ul>

    </div><!-- /.navbar-collapse -->

  @endguest

  </div><!-- /.container-fluid -->

</nav>