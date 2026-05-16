@include('template.partials.cabecera_general')

@include('flash::message')
	
{{-- Muestra msgs de error retornados por los Request personalisados --}}
@include('template.partials.errors')

<div class="content">
	@yield('contenido')
</div> <!-- Fin.container del Template -->

@include('template.partials.pie_general')

@yield('scrip')

</body>
</html>