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
    </div>     <!-- Fin Header -->

    <!-- Si no esta Conectado Authentication Links -->
    @guest
      <div class="navbar-header pull-right navbar-right">
         <a class="navbar-brand" href="{{ route('login') }}"><i class="glyphicon glyphicon-log-in"></i><strong> Ingreso</strong></a>
      </div>

    @else

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div id="navbar" class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Opciones <span class="caret"></span></a>
              <ul class="dropdown-menu">
                  <li><a href="{{ route('ot.consulta') }}">Consulta OT</a></li>
                  <li><a href="{{ route('ot.index') }}">Ordenes de Trabajos</a></li>
                      <li role="separator" class="divider"></li>

                <li><a href="{{ route('compras.index') }}">Compras</a></li>
                <li><a href="{{ route('sucursales.lista_remitos') }}">Remitos InterSucursales</a></li>
                <li><a href="{{ route('productos.index') }}">Productos</a></li>                
 
                <li><a href="{{ route('productos.publicaciones') }}">Publicaciones OnLine</a></li>                               
                <li><a href="{{ route('clientes.index') }}">Clientes</a></li>          
                <li><a href="{{ route('proveedores.index') }}">Proveedores</a></li> 
                <li><a href="{{ route('facturas.index') }}">Facturas</a></li>

                <li role="separator" class="divider"></li>
                <li><a href="{{ route('productos.consultaprecio') }}">Consulta de Precios</a></li>
                <li><a href="{{ route('productos.modificaprecio') }}">Modificacion de Precios</a></li>

                <!-- Opciones Solo si es Administrador -->
                @if(Auth::user() and Auth::user()->perfil_id == 'ADM' )
                  <li role="separator" class="divider"></li>
                  <li><a href="{{ route('cajas.altas') }}">Alta de Movimientos Caja</a></li>
                  <li><a href="{{ route('cajas.transferencias') }}">Transferencias Cajas</a></li>
                  <li><a href="{{ route('ventas.altas') }}">Alta de Ventas-Facturador</a></li>
                  @endif

            <!-- Opciones Solo si es Administrador-->
            @if(Auth::user()->perfil_id == 'ADM')
            <li class="dropdown"> <!-- Opciones Informes -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Informes <span class="caret"></span></a>
              <ul class="dropdown-menu">
                  <li><a href="{{ route('cajas.ventas') }}">Ventas</a></li>
                  <li><a href="{{ route('cierres.index') }}">Cierres de Caja</a></li>  

                  <li><a href="{{ route('cierres.saldosCuentasDetalle') }}">Saldos de Cuentas </a></li>

                <li><a href="{{ route('productos.movimientos') }}">Movimientos de Productos</a></li><li><a href="{{ route('ventas.forma_pago') }}">Formas de Pago</a></li>

                <li><a href="{{ route('estadisticas.consolidado') }}">Consolidado</a></li>

                <li role="separator" class="divider"></li>
                <li><a href="{{ route('estadisticas.codmov') }}">Estadistica Ingresos/Egresos</a></li>
                <li><a href="{{ route('estadisticas.rubro') }}">Estadistica por Rubros</a></li>
                <li><a href="{{ route('estadisticas.ot') }}">Estadistica Ordenes Trabajos</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="{{ route('productos.planilla_cristales') }}">Planilla de Cristales</a></li>

              </ul>
            </li> <!-- Fin de Informes -->
          @endif <!-- Fin de ADM -->              
 <!--  AQUI BORRE              

 -->
 



</ul>

      <ul class="nav navbar-nav navbar-right">

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