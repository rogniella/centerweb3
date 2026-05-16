@extends('template.main')
@section('titulo','Editar Produto')
@section('scrip')
  
<!-- Mini Editor de Texto Ekko Lightbox -->
<script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>

<script>

  $(function () {

    //uso de lightbox
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox({
        alwaysShowClose: true
      });
    });
  });

  function cancelar() {
      window.close();
  }


</script> 

@endsection


@section('contenido')

<!-- Ekko Lightbox -->
<link rel="stylesheet" href="{{ asset('plugins/ekko-lightbox/ekko-lightbox.css') }}">

<div id="userModal"  >
<div class="modal-dialog modal-lg" role="document">

<form action="{{ route('productos.store',$producto->id) }}" method="POST"  class="form-horizontal"   role="form" enctype="multipart/form-data" >
  @csrf

   <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" onclick="cancelar()">&times;</button>
        <h4 id="modal-title"> Editar Produto: {!! $producto->Prod_Familia !!}-{!! $producto->Prod_Id !!}    {!! $producto->Prod_Descripcion !!} &nbsp;</h4>
    </div>

<ul class="nav nav-pills" role="tablist">
  <li role="presentation" class="active" >
     <a class="nav-link" data-toggle="pill" href="#datos">Datos</a>
  </li>
  <li role="presentation"  >
     <a class="nav-link" data-toggle="pill" href="#movi">Movimientos</a>
  </li>
  <li role="presentation" >
    <a class="nav-link" data-toggle="pill" href="#auditoria">Auditoria</a>
  </li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade" id="auditoria" role="tab-panel" >
    <div class="modal-body">
        <table id="tabla_auditoria"
           data-height= "450"
           data-toggle="table"
           data-cache = "false"
           data-pagination="true"
           data-page-size="30"
           data-page-list=""
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th data-field="HisProd_Fecha" data-footer-formatter="idTotal" data-sortable="true">Fecha-Hora </th>
            <th data-field="HisProd_Campo" data-halign="center" data-sortable="true">Propiedad</th>
            <th data-field="HisProd_ValorAnt" data-halign="center"  data-sortable="true"
              >Valor Ant</th>
            <th data-field="HisProd_ValorNvo" data-halign="center" data-sortable="true"
             >Valor Nvo</th>
            <th data-field="HisProd_Usuario" data-sortable="true" >Usuario</th>
            <th data-field="HisProd_SucursalOrig" data-sortable="true" data-align="center" >Suc.Origen</th>
          </tr>
          </thead>
       </table>     
      </div> <!-- FIN Modal body -->
  </div> <!-- FIN TAB AUDITORIA -->

  <div class="tab-pane fade" id="movi" role="tab-panel">
      <div class="modal-body">
          <table id="tabla_movi"
           data-toggle="table"
           data-cache = "false"
           data-pagination="true"
           data-page-size="50"
           data-page-list=""
           class="table table-striped table-condensed"
          >
          <thead>
          <tr> 
            <th data-field="Mov_FecMov" data-footer-formatter="idTotal" data-sortable="true">Fecha-Hora </th>
            <th data-field="Mov_Operacion" data-halign="center" data-align="center" data-sortable="true"
             >Operacion</th>
            <th data-field="Mov_Cantidad" data-halign="center" data-align="center" data-sortable="true"
             >Cantidad</th>
            <th data-field="Mov_PrecioUnitario" data-halign="center" data-align="right" data-sortable="true"
             >Precio Unit.</th>
            <th data-field="Mov_Precio" data-halign="center" data-align="right" data-sortable="true"
             >Total</th>
            <th data-field="Mov_Motivo" data-sortable="true" data-align="center" >Observación</th>
            <th data-field="Mov_UsuAlta" data-sortable="true" data-align="center" >Usuario</th>
            <th data-field="Mov_Sucursal" data-sortable="true" data-align="center" >Suc.Origen</th>
          </tr>
          </thead>
       </table>     
      </div> <!-- FIN Modal body -->
  </div> <!-- FIN TAB MOVIMIENTOS -->

  <div class="tab-pane fade show active in" id="datos" role="tab-panel">

    <div class="modal-body">

        <div class="form-group">
            <label class="control-label col-md-2 text-right">Descripción:</label>
            <div class="col-md-10">
                <input type="text" class="form-control" name="Prod_Descripcion" id="Prod_Descripcion" maxlength="50"  value = "{!! $producto->Prod_Descripcion !!}" required>
          </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Costo:</label>
            <div class="col-md-4">
                <input class="form-control text-right" type="number" id="Prod_Costo" name="Prod_Costo" value="{!! $producto->Prod_Costo !!}">
          </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Precio:</label>
            <div class="col-md-4">
                <input class="form-control text-right" type="number" id="Prod_Precio" name="Prod_Precio" value="{!! $producto->Prod_Precio !!}">
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label col-md-4 text-right">Precio 2:</label>
                <div class="col-md-8">
                    <input class="form-control text-right" type="number" id="Prod_Precio2" name="Prod_Precio2" value="{!! $producto->Prod_Precio2 !!}">
                </div>
              </div>
            </div>
        </div>
        <div class="form-group">
           <label class="control-label col-md-2 text-right">Marca:</label>
            <div class="col-md-4">
                 <select id="Prod_Marca" name="Prod_Marca" class="form-control">
                  <option value= "0">[Sin Clasificar]</option>
                 @foreach ($marcas as $marca)
                    @if ( $producto->Prod_Marca == $marca->id)
                      <option value="{!! $marca->id !!}" selected="selected">{!! $marca->nombre !!}</option>
                    @else
                      <option value="{!! $marca->id !!}" >{!! $marca->nombre !!}</option>
                    @endif                       
                 @endforeach
                 </select>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label col-md-4 text-right">Estado:</label>
                <div class="col-md-8">
                <select id="Prod_Estado" name="Prod_Estado" class="form-control">
                  <option value="A">Activo</option>
                  @if ( $producto->Prod_Estado == 'I')
                    <option value="I" selected="selected">Inactivo</option>
                  @else
                    <option value="I">Inactivo</option>
                  @endif                  
                </select>
                </div>
              </div>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-2">Añadir imágenes:</label>
            <div class="col-md-10">
               <input type="file" class="form-control-file" name="imagenes[]" id="imagenes[]" multiple 
               accept="image/*" >
               
               <div class="alert alert-warning" role="alert">
                Un número ilimitado de archivos pueden ser cargados en este campo. 
                 <br>
                 Tipos permitidos: jpeg, png, jpg, gif, svg.
                 <br>
               </div>
            </div>        
        </div>

        <div class="form-group">
            <label class="control-label col-md-2">Especificaciones:</label>
            <div class="col-md-10">

            <textarea class="form-control ckeditor" name="especificaciones" id="especificaciones" rows="3">
                    {!! $producto->especificaciones !!}                    
            </textarea>
           
            </div>        
        </div>

        <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Galeria de imagenes</h3>
            </div>

          <div class="panel-body">
            <div class="row">

              @foreach ($producto->images as $image)
              <div id="idimagen-{{$image->id}}" class="col-sm-2">
                <a href="{{  asset($image->url) }}" data-toggle="lightbox" data-title="Id:{{ $image->id }}"  data-gallery="gallery">
                  <img style="width:150px; height:150px;" src="{{ asset($image->url) }}" class="img-fluid mb-2" />
                </a>
                <br>
                <a href="{{ asset($image->url) }}"
                    v-on:click.prevent="eliminarimagen({{ $image }} )"
                  >
                  <i class="fas fa-trash-alt" style="color:red"></i> Id:{{ $image->id }}
                </a>
              </div>
              @endforeach
                           
            </div>
          </div>
        </div>


     </div> <!-- FIN Modal body -->
   </div> <!-- FIN TAB  DATOS -->
 
   </div> <!-- FIN CONTENEDOR TAB -->

    <div class="modal-footer">
        <input type="hidden" id="operation" name="operation" value="update" >
        <input type="hidden"  name="id" id="id" value="{!! $producto->Prod_idWEB !!}">
        <input type="submit" id="btn-submit" class="btn btn-success" value="Aceptar">
        <button type="button" class="btn btn-default" onclick="cancelar()">Cancelar</button>
    </div>
 </div>

</form>

</div> <!-- FIN Modal body -->
</div> <!-- FIN TAB  DATOS -->

@endsection