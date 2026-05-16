<!-- Auxiliar para Armar Cuadro Html -->


    <!-- Defino las pestañas de la ficha -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active" >
            <a class="nav-link" data-toggle="tab" href="#datos">Datos</a>
        </li>
        <li role="presentation"  >
            <a class="nav-link" data-toggle="tab" href="#detalle_pagos">Detalles</a>
        </li>
    </ul>

    <!-- Defino Contenido de las pestañas de la ficha -->
    <div class="tab-content">
      <div class="tab-pane fade" id="detalle_pagos" role="tab-panel" >
		<div class="alert alert-info" role="alert">Productos</div> 
        <table 
           data-toggle="table"
           data-height= "450"
           data-cache = "false"
           data-pagination="true"
           data-page-size="30"
           data-page-list=""
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th  data-halign="center" data-sortable="true">Producto</th>
            <th  data-halign="center" data-sortable="true">Descripción</th>
            <th  data-align="right" data-sortable="true">Monto</th>
          </tr>
          </thead>
		  <tbody>
			@foreach($ot->DetalleProductos as $fila)
				<tr>
  					<td>{!! $fila->Mov_Familia . " " . $fila->Mov_IdProd  !!}</td>
					<td>{!! $fila->Mov_Descripcion !!}</td>
					<td>{!! $fila->Mov_Precio !!}</td>
				</tr>
			@endforeach
		   </tbody>
		</table>   
        <br>
		<div class="alert alert-info" role="alert">Pagos</div> 
        <table id="tabla_auditoria"
           data-toggle="table"
           data-height= "450"
           data-cache = "false"
           data-pagination="true"
           data-page-size="30"
           data-page-list=""
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th  data-sortable="true">Fecha-Hora </th>
            <th  data-halign="center" data-sortable="true">Descripción</th>
            <th data-halign="center"  data-sortable="true">Moneda</th>
            <th  data-halign="center" data-sortable="true">Monto</th>
          </tr>
          </thead>
		  <tbody>
			@foreach($ot->DetallePagos as $fila)
				<tr>
					<td>{!! $fila->Caj_FecMov !!}</td>
					<td>{!! $fila->Caj_Detalle !!}</td>
					<td>{!! $fila->Caj_Moneda !!}</td>
					<td>{!! $fila->Caj_Monto !!}</td>
				</tr>
			@endforeach
		   </tbody>
		</table>   
		
		

      </div> <!-- FIN TAB DETALLE PAGOS -->

    <div class="tab-pane fade show active in" id="datos" role="tab-panel">
    <br>	  

	<div class="row">
		<div class="col-md-12">
		<table>
		<tbody>

		  @foreach($datos as $fila)
		      <tr>
		          <td>{!! $fila['titulo'] !!}</td>
		          <td>{!! $fila['valor'] !!}</td>
		      </tr>
		  @endforeach
		</tbody>
		</table>

		</div>       
	</div>
    <br>
    <a href="javascript:send_demora( '{!!$ot->Cliente->Cli_Pais!!}{!!$ot->Cliente->Cli_Telefono!!}' );">Informar Demora</a> 

	</div> <!-- FIN TAB  DATOS -->
 
 </div> <!-- FIN CONTENEDOR TAB -->
