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
      <a class="navbar-brand" href="{{ route('home') }}"><strong>Center FotoOptica</strong></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="navbar" class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">

            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Opciones <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <!-- Opciones Solo si esta conectado -->
                @if(Auth::user())
                  <li><a href="{{ route('clientes.informecc') }}">Cuenta Corriente</a></li>
                @endif  
              </ul>
            </li> <!-- Fin de Opciones -->




    </ul>

    <ul class="nav navbar-nav navbar-right">


        <!-- Si no esta Conectado Authentication Links -->
        @guest
          <li><a href="#"><span class="glyphicon glyphicon-user"></span> Invitado</a></li>
          <li><a href="{{ route('login') }}"><span class="glyphicon glyphicon-log-in"></span>  Ingreso con Clave</a></li>
        @else
          <li class="dropdown">          
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <span class="glyphicon glyphicon-user"></span> {{ Auth::user()->name }}      <span class="caret"></span></a>
            <ul class="dropdown-menu">

                <li><a href="http://www.centerfotooptica.com.ar">Cerrar Sesión</a></li>
                <li><a href="{{url('user/password')}}">Cambiar Contraseña</a></li>
              <li role="separator" class="divider"></li>

              <li><a href="{{ route('logout')}}" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
              </li>
    
        </ul>

      @endguest
      
    </div><!-- /.navbar-collapse -->

  </div><!-- /.container-fluid -->
</nav>