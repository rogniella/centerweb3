@include('template.partials.cabecera_general')
<!-- Opciones Solo si esta conectado -->
@if(Auth::user()  AND Auth::user()->perfil_id == 'CLI' )
    <!-- Menu principal -->
    @include('template.partials.menu_cliente')
@else
    <!-- Menu principal -->
    @include('template.partials.menu_center')
@endif

@include('flash::message')
	
{{-- Muestra msgs de error retornados por los Request personalisados --}}
@include('template.partials.errors')

<!--  No dejaba Margen     <div class="content">    -->
<div class="container-fluid">
    @yield('contenido')
</div> <!-- Fin.container del Template -->

@include('template.partials.pie_general')

@yield('scrip')

</body>
</html>