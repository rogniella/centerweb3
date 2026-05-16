@include('template.partials.cabecera_general')

@include('flash::message')
	
{{-- Muestra msgs de error retornados por los Request personalisados --}}
@include('template.partials.errors')

<!--  No dejaba Margen     <div class="content">    -->
<div class="container-fluid">
<br>    
<div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">@yield('titulo','Center')</h3>
            </div>
            <div class="panel-body">
                    @yield('contenido')
            </div> <!-- Fin Panel BodyInfo -->
    </div> <!-- Fin Panel Info -->
</div> <!-- Fin.container del Template -->

@include('template.partials.pie_general')

@yield('scrip')

</body>
</html>