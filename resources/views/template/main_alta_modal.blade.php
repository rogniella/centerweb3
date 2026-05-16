@include('template.partials.cabecera_general')
<!-- Menu principal -->
@include('template.partials.menu_center')
  
@include('flash::message')
	
{{-- Muestra msgs de error retornados por los Request personalisados --}}
@include('template.partials.errors')

<!-- Morris Charts CSS -->
<link rel="stylesheet" href="{{ asset('plugins/morris-js/morris.css')}}">

<!-- DateRangepicker  Rango de Fechas -->
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-daterangepicker/daterangepicker.css') }}">

<div class="container-fluid">
	@yield('contenido')
</div> <!-- Fin.container del Template -->

@include('template.alta_modif_modal')


@include('template.partials.pie_general')

@include('template.alta_modif_modal_js')



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