@section('primer_campo','Prod_CodBarra')  <!-- Es el campo que se selecciona al abrir la ventana -->

<!-- Formulario de Alta/Modificacion Productos -->
<div id="userModal" class="modal fade" >
 <div class="modal-dialog modal-lg" role="document">
  <form method="post" id="save-modify-form" class="form-horizontal"   role="form">
   <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 id="modal-title">&nbsp;</h4>
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
          <label class="control-label col-md-2">Código Barra:</label>
          <div class="col-md-8">
                <input type="text" class="form-control" name="Prod_CodBarra" id="Prod_CodBarra" maxlength="20">
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-md-2 text-right">Familia:</label>
          <div class="col-md-4">
              <select name="Prod_Familia" id="Prod_Familia" class="form-control">
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == '' ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label col-md-4 text-right">Código:</label>
              <div class="col-md-8">
               <div class="input-group"> 
                <input type="text" class="form-control" name="Prod_Id" id="Prod_Id" required maxlength="50">
                 <span class="input-group-btn">
                    <button type="button" class="btn" title="Nuevo Articulo"
                          onclick="NuevoProducto()">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>     
                 </span>

               </div>  
              </div>
            </div>
          </div>
          
        </div>
        <div class="form-group">
            <label class="control-label col-md-2 text-right">Descripción:</label>
            <div class="col-md-10">
                <input type="text" class="form-control" name="Prod_Descripcion" id="Prod_Descripcion" maxlength="50" required>
          </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Costo:</label>
            <div class="col-md-4">
                <input class="form-control text-right" type="number" id="Prod_Costo" name="Prod_Costo" value="">
          </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Precio:</label>
            <div class="col-md-4">
                <input class="form-control text-right" type="number" id="Prod_Precio" name="Prod_Precio" value="">
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label col-md-4 text-right">Precio 2:</label>
                <div class="col-md-8">
                    <input class="form-control text-right" type="number" id="Prod_Precio2" name="Prod_Precio2" value="">
                </div>
              </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Estado:</label>
            <div class="col-md-4">
                <select id="Prod_Estado" name="Prod_Estado" class="form-control">
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
          </div>
        </div>
     </div> <!-- FIN Modal body -->
   </div> <!-- FIN TAB  DATOS -->
 


   </div> <!-- FIN CONTENEDOR TAB -->

    <div class="modal-footer">
        <div id="msgErrModal"  class="alert alert-danger" align="left" >completa por programa</div>
            <input type="hidden"  name="id" id="id" value="0">
            <input type="hidden" id="operation">
            <input type="submit" id="btn-submit" class="btn btn-success" value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
    </div>
  </form>  
 </div>
</div> <!-- FIN Formulario de Alta/ Modificacion -->
