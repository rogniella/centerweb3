<!-- Formulario de Alta -->
<div id="altaModal" class="modal fade" >
 <div class="modal-dialog modal-lg" role="document">
  <form method="post" id="alta-form" class="form-horizontal"   role="form">
   <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 id="modal-title">&nbsp;</h4>
    </div>

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
</div> <!-- FIN Formulario de Alta -->
