
<!-- Formulario de Alta/ Modificacion -->
<div id="userModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
      <form method="post" id="save-modify-form" class="form-horizontal"   role="form">

          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 id="modal-title">&nbsp;</h4>
            </div>

            @yield('formulario_alta_modificacion')

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

@yield('formulario_otros')
