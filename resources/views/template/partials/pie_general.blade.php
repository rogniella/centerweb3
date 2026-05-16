    <div class="modal fade" id="modalerror">
  			<div class="modal-dialog">
        		<div class="alert alert-danger">
        			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
           			<b> Error :</b>
           			<div id="lblerror">...completa llamado...</div>
        		 <div class="modal-footer">
           			<button type="button" class="btn btn-warning" data-dismiss="modal">Cerrar</button>
        		 </div>
        		</div>
   			</div><!-- /.modal-dialog TIENE QUE IR -->
		</div><!-- /.modal -->
   
    <div class="modal fade" id="modalran">
  			<div class="modal-dialog">
        		<div id="modalRanTipo">
        			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
           			<div id="modalRanMsg">...completa llamado...</div>
        		</div>
   			</div><!-- /.modal-dialog TIENE QUE IR -->
		</div><!-- /.modal -->

	<footer class="admin-footer">
		<nav class="navbar navbar-desault">
			<div class="container-fluid"  style="background: #D3D3D3">
				<div class="collapse navbar-collapse">
          <p class="navbar-text navbar-left"><b> GESTIÓN WEB {{ env('VERSION')}}    Base: {{ env('DB_DATABASE')}} </b></p>
					<p class="navbar-text"> {{date('d/m/Y')}}</p>
           @if(Auth::user()  )
              <p class="navbar-text navbar-right">  <b style="color:blue";> {{ Auth::user()->perfil_id }}  Sucursal Ope: {{ Auth::user()->sucursal }} </b>  &nbsp;&nbsp; RAN System</p>         
                       
           @else 
             <p class="navbar-text navbar-right"><b>RAN System</b></p>
           @endif   
				</div>
			</div>
		</nav>
	</footer>

  <!-- Estan en /public/plugins -->  
	<script src="{{ asset('plugins/jquery/js/jquery-3.1.0.js') }}"></script>

  <!-- Para que permita mover la ventanas modales   https://jqueryui.com/ -->  
  <script src="{{ asset('plugins/jquery-ui-1.12.1/jquery-ui.min.js') }}"></script>

	<script src="{{ asset('plugins/bootstrap/js/bootstrap.js') }}"></script>
	<script src="{{ asset('plugins/chosen/chosen.jquery.js') }}"></script>
	<script src="{{ asset('plugins/trumbowyg/trumbowyg.js') }}"></script>

  <!-- Buscador autocomplete -->
  <script src="{{ asset('plugins/typeahead/bootstrap3-typeahead.min.js') }}"></script>

  <!-- Para los combos con busquedas -->
  <script src="{{ asset('plugins/bootstrap-select-1.12.4-dist/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('plugins/bootstrap-select-1.12.4-dist/js/defaults-es_ES.min.js') }}"></script>

	<!-- Para tablas -->
  <script src="{{ asset('plugins/bootstrap-table-1.14.2-dist/bootstrap-table.min.js') }}"></script>
  <script src="{{ asset('plugins/bootstrap-table-1.14.2-dist/bootstrap-table-es-ES.min.js') }}"></script>  <!-- Traducir los msj al espa�ol -->

    <!-- Extensiones de tablas  -->
    <!--   Para Imprimir -->
    <script src="{{ asset('plugins/bootstrap-table-1.14.2-dist/extensions/print/bootstrap-table-print.min.js') }}"></script>
    <!--   Para exportar a PDF y Excel   Usa Plugin: tableExport.jquery.plugin-->
    <script src="{{ asset('plugins/bootstrap-table-1.14.2-dist/extensions/export/bootstrap-table-export.min.js') }}"></script>
    <script src="{{ asset('plugins/tableExport/tableExport.min.js') }}"></script>
    <script src="{{ asset('plugins/tableExport/jsPDF/jspdf.min.js') }}"></script>
    <script src="{{ asset('plugins/tableExport/jsPDF-AutoTable/jspdf.plugin.autotable.js') }}"></script>
	
    <!-- Para bloquiear pantalla cuando llamamos a otros   -->
    <script src="{{ asset('plugins/js/jquery.blockUI.js') }}"></script>    

    <!-- Funciones Comunes Propias   -->
    <script src="{{ asset('js/funciones_js.js') }}"></script>    

    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js')}}"></script>

    <!-- Incluir AutoNumeric.js desde CDN  -->
    <script src="{{ asset('plugins/js/autoNumeric.min.js')}}"></script>  <!-- Para traducir los mensajes al español -->
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>

  <script>

    $(function() {
         //Se pone para que en todos los llamados ajax se bloquee la pantalla mostrando el mensaje Procesando...
         $.blockUI.defaults.message = '<h3>Procesando...</h3>';
         $(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
    });

    //Para que se seleccione los input
    $("input[type=text]").focus(function(){    
      this.select();
    });

    function msgerror2(msg,tiempo) {

      Swal.fire({
                type: 'error',
                title: 'Error: ' + msg,
                text: '',
                footer: ''
            })
            return true;	
    }

    function msgerror(msg,tiempo) {
		 
	     $('#lblerror').html(msg);
	     $('#modalerror').modal('show');
       
       // Si marco el tiempo de cierre automatico
	   	 if(tiempo){
	         window.setTimeout(function(){
	        	 $('#modalerror').modal('hide');
	         }, tiempo);
	     }
	 	   	 
		   return true;	
    }

    function muestroMsg(msg,tiempo,cuadroChico) {
     
      $('#modalRanMsg').html(msg);
      $('#modalRanTipo').addClass('alert alert-success');
      if(cuadroChico){
        $('#modalRanTipo').addClass('modal-sm');
      }
      $('#modalran').modal('show');
      
      // Si marco el tiempo de cierre automatico
      if(tiempo){
           window.setTimeout(function(){
             $('#modalran').modal('hide');
           }, tiempo);
      }
         
      return true; 
    }

  </script>