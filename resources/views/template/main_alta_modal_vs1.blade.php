@include('template.partials.cabecera_general_vs1')
<!-- Menu principal -->
@include('template.partials.menu_center_vs1')
  
@include('flash::message')
	
{{-- Muestra msgs de error retornados por los Request personalisados --}}
@include('template.partials.errors')

<div class="content">
	@yield('contenido')
</div> <!-- Fin.container del Template -->

@include('template.alta_modif_modal')


@include('template.partials.pie_general_vs1')

@include('template.alta_modif_modal_js')

@yield('scrip')

</body>
</html>