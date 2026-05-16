<div class="row">
<div class="col-md-12">
<table>
<tbody>
  <tr><td><b>Cliente:</b></td> <td class="value">{{ $cliente->Cli_ApeNom }} ({{ $cliente->Cli_Id }})</td> </tr>
  <tr><td><b>Documento:</b></td> <td class="value">{{ $cliente->Cli_Documento}}</td> </tr>
  <tr><td><b>Teléfono:</b></td> <td class="value">{{ $cliente->Cli_Telefono}}</td> </tr>
  <tr><td><b>Sucursal:</b></td> <td class="value">{{ $cliente->Cli_Sucursal}}</td> </tr>
  
</tbody>
</table>
</div>       
</div>

<br>
<b>Ordenes de Trabajo:</b>
<table id="ottabla"
           data-toggle="table"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list=""      
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="tipoOT" data-align="center"  data-sortable="true">Tipo</th>
            <th data-field="id"  data-formatter="fotmatoColSel" data-halign="center" data-align="center" data-sortable="true">Nro.OT</th>
            <th data-field="fecha"  data-halign="center" data-align="center" data-sortable="true">Fecha</th>
            <th data-field="estado" data-halign="center" data-align="center" data-sortable="true">Estado </th>
            <th data-field="precio" data-halign="center" data-align="right" data-sortable="true">Monto</th>
            <th data-field="sucursal" data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
            <th data-field="idweb" data-visible="false">IdWeb</th>
          </tr>
          </thead>
          <tbody>
          @foreach($cliente->ots as $ot)
				<tr>
					<td>{{ $ot->Ot_Tipo}}</td>
					<td>{{ $ot->Ot_Id}}</td>
					<td>{{ $ot->Ot_FecPedido}}</td>
					<td>{{ $ot->Ot_Estado}}</td>
					<td>{{ $ot->Ot_Precio}}</td>
					<td>{{ $ot->Ot_Sucursal}}</td>
					<td>{{ $ot->Ot_idWEB}}</td>
				</tr>
			@endforeach
     </tbody>
   </table>

   <br>
<b>Compras:</b>
<table id="vtatabla"
           data-toggle="table"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list=""      
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="tipo" data-align="center"  data-sortable="true">Tipo</th>
            <th data-field="idvta"   data-formatter="fotmatoColSel" data-halign="center" data-align="center" data-sortable="true">Nro.</th>
            <th data-field="fecha"  data-halign="center" data-align="center" data-sortable="true">Fecha</th>
            <th data-field="codigo" data-halign="center" data-align="left" data-sortable="true">Estado </th>
            <th data-field="monto" data-halign="center" data-align="right" data-sortable="true">Monto</th>
            <th data-field="sucursal" data-halign="center" data-align="center" data-sortable="true">Sucursal</th>
          </tr>
          </thead>
          <tbody>
          @foreach($cliente->ventas as $vta)
				<tr>
					<td>{{ $vta->Comp_TipoOT}}</td>
					<td>{{ $vta->Comp_idWEB}}</td>
					<td>{{ $vta->Comp_FecMov}}</td>
					<td>{{ $vta->Comp_Estado}}</td>
					<td>{{ $vta->Comp_Monto}}</td>
          <td>{{ $vta->Comp_Sucursal}}</td>
				</tr>
			@endforeach
     </tbody>
   </table>

