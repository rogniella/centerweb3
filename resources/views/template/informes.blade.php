@include('template.partials.cabecera_general')
<!-- Opciones Solo si esta conectado -->
@if(Auth::user()  AND Auth::user()->perfil_id == 'CLI' )
        <!-- Menu principal -->
        @include('template.partials.menu_cliente')
@else
    <!-- Menu principal -->
    @include('template.partials.menu_center')
@endif  

<!-- Morris Charts CSS -->
<link rel="stylesheet" href="{{ asset('plugins/morris-js/morris.css')}}">

<!-- DateRangepicker  Rango de Fechas -->
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-daterangepicker/daterangepicker.css') }}">

	
{{-- Muestra msgs de error retornados por los Request personalisados --}}
@include('template.partials.errors')

<div class="container-fluid">
	@yield('contenido')
</div> <!-- Fin.container del Template -->

@include('template.partials.pie_general')


<!-- Para Agrupar las tablas -->
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-table-1.14.2-dist/extensions/group-by-v2/bootstrap-table-group-by.css') }}">
<script src="{{ asset('plugins/bootstrap-table-1.14.2-dist/extensions/group-by-v2/bootstrap-table-group-by.js') }}"></script>


<!-- daterangepicker  Rango de Fechas -->

<script src="{{ asset('plugins/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('plugins/moment/locale/es.js') }}"></script> 
<script src="{{ asset('plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

<!-- Morris Charts JavaScript -->  
<script src="{{ asset('plugins/morris-js/raphael.min.js')}}"></script>
<script src="{{ asset('plugins/morris-js/morris.min.js')}}"></script>


@yield('scrip')

</body>
</html>
