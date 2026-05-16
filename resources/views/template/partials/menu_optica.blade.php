<ul class="nav navbar-nav">
          <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"  aria-expanded="false">Opciones <span class="caret"></span></a>
              <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="{{ route('ot.consulta') }}">Consulta OT</a></li>
                  <li><a class="dropdown-item" href="{{ route('ot.index') }}">Ordenes de Trabajos</a></li>
                  <li role="separator" class="divider"></li>
                <li><a class="dropdown-item" href="{{ route('compras.index') }}">Compras</a></li>
                <li><a href="{{ route('sucursales.lista_pedidos') }}">Pedidos InterSucursales</a></li>
                <li><a href="{{ route('sucursales.lista_remitos') }}">Remitos InterSucursales</a></li>
                <li><a href="{{ route('productos.index') }}">Productos</a></li>                
 
                <li><a href="{{ route('productos.publicaciones') }}">Publicaciones OnLine</a></li>                               
                <li><a href="{{ route('clientes.index') }}">Clientes</a></li>          
                <li><a href="{{ route('proveedores.index') }}">Proveedores</a></li>          
                <li><a href="{{ route('facturas.index') }}">Facturas</a></li>

                <li role="separator" class="divider"></li>
                <li><a href="{{ route('productos.consultaprecio') }}">Consulta de Precios</a></li>
                <li><a href="{{ route('productos.modificaprecio') }}">Modificacion de Precios</a></li>
                <li role="separator" class="divider"></li>
 
                <!-- Opciones Solo si es Administrador -->
                  @if(Auth::user() and Auth::user()->perfil_id == 'ADM' )
                  <li><a href="{{ route('cajas.altas') }}">Alta de Movimientos Caja</a></li>
                  <li><a href="{{ route('cajas.transferencias') }}">Transferencias Cajas</a></li>
                  @endif
                <li><a href="{{ route('ventas.altas') }}">Alta de Ventas-Facturador</a></li>


              </ul>
            </li> <!-- Fin de Opciones -->

          <!-- Opciones Solo si es Administrador-->
          @if(Auth::user()->perfil_id == 'ADM')
            <li class="dropdown"> <!-- Opciones Informes -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Informes <span class="caret"></span></a>
              <ul class="dropdown-menu">
                  <li><a href="{{ route('cajas.ventas') }}">Ventas</a></li>
                  <li><a href="{{ route('cajas.cierres') }}">Cierres de Caja</a></li>  

                  <li><a href="{{ route('cajas.saldosCuentasDetalle') }}">Saldos de Cuentas </a></li>

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
          <li class="dropdown"> <!-- Opciones Mantenimiento -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Mantenimiento <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="{{ route('ctrol_stock.index') }}">Control de Stock</a></li>
                <!-- Opciones Solo si es Administrador-->
                @if(Auth::user()->perfil_id == 'ADM')
                  <li role="separator" class="divider"></li>
                  <li><a href="{{ route('tarjetas.index') }}">Procesar Archivos de Tarjetas</a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="{{ route('productos.actualiza_precio') }}">Actualizar Precios Cristales</a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="{{ route('minformes.index') }}">Tipos de Informes</a></li>
                  <li><a href="{{ route('monedas.index') }}">Monedas</a></li>
                  <li><a href="{{ route('users.index') }}">Usuarios</a></li>
                @endif <!-- Fin de ADM -->
              </ul>
            </li> <!-- Fin de Mantenedores -->
      </ul>

      <ul class="nav navbar-nav navbar-right">

        <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Servicios <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="https://coopervisionlatam.com/practitioner/herramientas-y-calculadoras/calculadora-de-conversion-de-anteojos" target="_blank">Graduaciones Lentes de Contacto</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="{{ route('servicios.index')}}?msg=OSDE">Osde</a></li>
                <li><a href="{{ route('servicios.index')}}?msg=JERA">Jerarquicos</a></li>
                <li><a href="{{ route('servicios.index')}}?msg=SANCOR">SancorSalud</a></li>                
                <li><a href="{{ route('servicios.index')}}?msg=OSPJN">Poder Judicial</a></li>                
                <li role="separator" class="divider"></li>
                <li><a href="http://www.hotmail.com" target="_blank" >HotMail</a></li>
              </ul>
        </li> <!-- Fin de Servicios -->
    </ul>    
