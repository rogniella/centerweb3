<!-- Formulario modal AltaCristal, se comparte en mas de una vista -->
<div id="userModal_AltaCristal" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <form method="post">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Agregar Cristal </h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-3 col-md-3">
                <label>Material</label>
                <br>
                <select class="form-control" name="id_material" id="id_material">
                    <option value="O" >Orgánico</option>
                    <option value="P" >Polycarbonato</option>
                    <option value="M" >Mineral</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-3">
                <label>Color</label>
                <br>
                <select class="form-control" name="id_color" id="id_color">
                    <option value="B" >Blanco</option>
                    <option value="A" >Blue Filter</option>
                    <option value="R" >AR</option>
                    <option value="F" >FotoCromático</option>
                    <option value="S" >Foto AR</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Unidades</label>
                <br>
                <input class="form-control text-right" type="number" name="unidad" id="unidad" value="1" >
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Grado Esf.</label>
                <br>
                <input class="form-control text-right" type="text" id="add_esf" value="">
            </div>
            <div class="col-lg-2 col-md-2">
                <label>Cil.</label>
                <br>
                <input class="form-control text-right" type="text" id="add_cil" value="">
            </div>

          </div> <!-- /Fin Row 1 Seleccion de Articulo -->
          <br>
          <div class="row">
          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msg2VentanaAddItem1" class="alert alert-warning" align="left" > </div>
            <div id="msg2VentanaAddItem" class="alert alert-success" align="left" > </div>
            <div id="msg2ErrorVentanaAddItem" class="alert alert-danger" align="left" hidden="true" ></div>

            <input id="btnAddCristal" type="button"  class="btn btn-success" onclick="ingresar_cristal()"  value="Aceptar">
            <button  type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>

        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de AltaCristal -->
