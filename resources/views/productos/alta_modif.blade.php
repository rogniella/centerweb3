
<!-- Formulario de Alta/Modificacion Productos -->


<div id="userModal" class="modal fade"  data-backdrop="static">
 <div class="modal-dialog modal-lg " role="document">
  <form method="post" id="save-modify-form-producto" class="form-horizontal"   role="form" enctype="multipart/form-data" >
   @csrf
  <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 id="modal-title">&nbsp;</h4>
    </div>

    <div class="modal-body">
      
    <!-- Defino las pestañas de la ficha -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active" >
            <a class="nav-link" data-toggle="tab" href="#datos">Datos</a>
        </li>
        <li role="presentation"  >
            <a class="nav-link" data-toggle="tab" href="#movi">Movimientos</a>
        </li>
        <li role="presentation" >
            <a class="nav-link" data-toggle="tab" href="#auditoria">Auditoria</a>
        </li>
        <li role="presentation" >
            <a class="nav-link" data-toggle="tab" href="#tienda">Tienda OnLine</a>
        </li>
    </ul>

    <!-- Defino Contenido de las pestañas de la ficha -->
    <div class="tab-content">
      <div class="tab-pane fade" id="auditoria" role="tab-panel" >
        <br>  
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
      </div> <!-- FIN TAB AUDITORIA -->

      <div class="tab-pane fade" id="movi" role="tab-panel">
        <br>  
        <table id="tabla_movi"
           data-toggle="table"
           data-height= "450"
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
            <th data-field="Mov_IdOT" data-halign="center" data-align="center" data-sortable="true"
             >Nro.Op</th>
            <th data-field="Mov_Cantidad" data-halign="center" data-align="center" data-sortable="true"
             >Cantidad</th>
            <th data-field="Mov_PrecioUnitario" data-halign="center" data-align="right" data-sortable="true"
             >Precio Unit.</th>
            <th data-field="Mov_Precio" data-halign="center" data-align="right" data-sortable="true"
             >Total</th>
            <th data-field="descripcion" data-sortable="true" data-align="center" >Observación</th>
            <th data-field="Mov_UsuAlta" data-sortable="true" data-align="center" >Usuario</th>
            <th data-field="Mov_SucursalDestino" data-sortable="true" data-align="center" >Suc.</th>
            <th data-field="Mov_Sucursal" data-sortable="true" data-align="center" >Suc.Origen Mov</th>
          </tr>
          </thead>
        </table>     
      </div> <!-- FIN TAB MOVIMIENTOS -->

      <div class="tab-pane fade" id="tienda" role="tab-panel">
        <br>
        <div class="form-group">
            <label class="control-label col-md-2 text-right">Descripción:</label>
            <div class="col-md-10">
                <input type="text" class="form-control" name="tienda_descripcion" id="tienda_descripcion" readonly>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Precio:</label>
            <div class="col-md-4">
                <input class="form-control text-right" type="number" id="tienda_precio" name="tienda_precio" value="">
          </div>
        </div>

      </div> <!-- FIN TAB TIENDA -->

      <div class="tab-pane fade show active in" id="datos" role="tab-panel">
        <br>
    <div class="alert alert-success" role="alert">        
        <div class="form-group">
          <label class="control-label col-md-2">Código Barra:</label>
          <div class="col-md-4">
                <input type="text" class="form-control" name="Prod_CodBarra" id="Prod_CodBarra" maxlength="20">
          </div>

          <div class="col-md-4">
              <div class="form-group">
                <label class="control-label col-md-4 text-right">Categoria:</label>
                <div class="col-md-8">
                  <input class="form-control" type="text" id="Prod_Categoria" name="Prod_Categoria"  value="">
                </div>
              </div>
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
      </div>
        <div class="form-group">
            <label class="control-label col-md-2 text-right">Descripción:</label>
            <div class="col-md-10">
                <input type="text" class="form-control" name="Prod_Descripcion" id="Prod_Descripcion" maxlength="50" required>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-2">Costo:</label>
            <div class="col-md-2">
                <input class="form-control text-right" type="number" id="Prod_Costo" name="Prod_Costo" step="1" value="">
            </div>
            <label class="control-label col-md-2">Precio:</label>
            <div class="col-md-2">
                <input class="form-control text-right" type="number" id="Prod_Precio" name="Prod_Precio" step="10" value="">
            </div>
            <label class="control-label col-md-2 text-right">Precio 2:</label>
            <div class="col-md-2">
                 <input class="form-control text-right" type="number" id="Prod_Precio2" name="Prod_Precio2" step="1" value="">
            </div>
        </div>

        <div class="form-group">
          <label class="control-label col-md-2 text-right"></label>
          <div class="col-md-10">
          <div class="alert alert-warning" role="alert" id="lblPrecio"  hidden="true" >
               </div>
          </div>
        </div>       


        <div class="form-group">
            <label class="control-label col-md-2">Stock Suc 1:</label>
            <div class="col-md-2">
                <input class="form-control text-right" type="number" id="stock1" name="stock1" step="1" value="">
            </div>
            <label class="control-label col-md-2 text-right">Stock Suc 2:</label>
            <div class="col-md-2">
                 <input class="form-control text-right" type="number" id="stock2" name="stock2" step="1" value="">
            </div>
        </div>
        

        <div class="form-group">
           <label class="control-label col-md-2 text-right">Marca:</label>
            <div class="col-md-2">
                 <select id="Prod_Marca" name="Prod_Marca" class="form-control">
                 </select>
            </div>
                <label class="control-label col-md-2 text-right">Estado:</label>
                <div class="col-md-2">
                 <select id="Prod_Estado" name="Prod_Estado" class="form-control">
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                 </select>
                </div>
        </div>


        <div class="form-group">
            <div class="col-md-2 text-right">
                 <a  class="tooltip-test" title="Ver/Agregar Imágenes" data-toggle="collapse" href="#collapseImagen" role="button" aria-expanded="false" aria-controls="collapseImagen">
                    Imágenes...
                 </a>
            </div>
          <div class="collapse" id="collapseImagen">

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


            <label class="control-label col-md-2 text-right">Imágenes:</label>
            <div class="col-md-10">
                <div id="data-imagenes"> </div>
            </div>
        </div> <!-- FIN IMAGENES -->

      </div> <!-- FIN GRUPO -->


      </div> <!-- FIN TAB  DATOS -->
 
    </div> <!-- FIN CONTENEDOR TAB -->

    </div> <!-- FIN Modal body -->

    <div class="modal-footer">
        <div id="msgErrModal"  class="alert alert-danger" align="left" >completa por programa</div>
        <input type="hidden" id="operation" name="operation" >
        <input type="hidden"  name="id" id="id" value="0">
        <input type="submit" id="btn-submit" class="btn btn-success" value="Aceptar">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
    </div> <!-- FIN Footer -->

  </div> <!-- FIN modal-content -->
  </form>  
 </div>  <!-- FIN modal-dialog -->
</div> <!-- FIN Formulario de Alta/ Modificacion -->

@section('scrip_alta_modif')

<script>

    $("#userModal").draggable({
       handle: ".modal-header"
    });


    //  JS de Formulario de Alta/ Modificacion 
      /**
     * Muestra el form de carga para el alta, ó Modificacion presentando los datos
     * en caso de que se proporcione por param. el id del registro a editar.
     * Para ambos casos se prepara según el tipo de operación a efectuar.
     * @param   is_modif        boolean     True si es modificar, false si es alta.
     * @param   id_registro     int         Id del registro para el que mostrar datos.
     */
    function showEditModalProducto(is_modif, id_registro) {

        var form = $("#save-modify-form-producto")[0];

        // Vaciar el formulario del modal.
        form.reset();
        // Seleccionar 1ra pestaña
        $('.nav-tabs a:first').tab('show')
        // o $('.nav-pills a[href="#datos"]').tab('show')
        $('#data-imagenes').html('');  //limpiar imagenes  

        $("#operation").val("store"); //Indica es un alta
        $("#modal-title").text("Nuevo Producto");
        $("#msgErrModal").hide();
        $("#lblPrecio").hide();


        if (is_modif) {
            // Si es Modificacion lee el registro para completar los campos
            var formdata = {
                id:         id_registro
            };
            $("#operation").val("update");
            $("#modal-title").text("Modificar Producto");

            $.ajax({
                dataType: "json",type:  'get', data: formdata,
                url:  '../{{$modulo_abm}}/show',            
                success: function(data){
                        var row;
                        row = data.result;
                        // Rellenar los datos sobre el registro a editar.
                        @foreach($campos_pantalla as $campoaux)
                            $("#{{$campoaux['name']}}").val(row.{{$campoaux['name']}});
                        @endforeach
                        buscarMarcas( row.Prod_Familia , 'N', row.Prod_Marca)
                        if (data.lblPrecio != '') {
                            $('#lblPrecio').html(data.lblPrecio);      
                            $("#lblPrecio").show();
                        }
                        if (data.tienda_descripcion != '') {
                          $('#tienda_descripcion').val(data.tienda_descripcion);
                          $('#tienda_precio').val(data.tienda_precio);
                        }

                        $('#data-imagenes').html(data.imagenes);      
                        $("#id").val(data.id);                
                },
                error:  function(xhr,err){ 
                        // Como estamos en el "callback" del done(), el modal para entonces ya
                        // estaría abierto. Lo cerramos.
                        $("#userModal").modal("hide");
                        if (xhr.status == 401) { // Si se desconecto
                            document.location.reload(); // Para que recargue y pida login
                        }else{
                            msgerror( xhr.responseText);
                        }    
                } // Fin si hay error
            }); // Fin llamado Ajax
        }else{
            // Alta
            buscarMarcas( $("#Prod_Familia").val()  , 'N' ,'')
        }


        $("#userModal").modal("show")
            // Cuando termina de mostrarse.
            .on("shown.bs.modal", function(e) {
            $("#Prod_CodBarra").select(); // Selecciona todo el texto del 1er campo.
        });
    }


    // Uno de los usos importantes del ready() es de declarar allí los "listeners"
    // de eventos como submit, click, etc.
    // También se utiliza para inicializar componentes de javascript.
    $(document).ready(function() {

        // Al Aceptar el formulario de nuevo/modif.
        $("#save-modify-form-producto").on("submit", function(event) {

            // Cancelar. Dado que vamos a manejar nosotros el envío.
            event.preventDefault();

            // En el form está definido el tipo de operación.
            var action_name = '../{{$modulo_abm}}/store';
 

      var datos = $(this).serializeArray(); //datos serializados
      var imagen = new FormData($("#save-modify-form-producto")[0]);

      console.log(datos)  
      //agergaremos los datos serializados al objecto imagen
      $.each(datos,function(key,input){
        imagen.append(input.name,input.value);
      });

      console.log(imagen)  

            $.ajax({
                dataType: "json",type:  'post',
//                data: new FormData(this),  //Asi para que acepte las imagenes
                data: imagen,  //Asi para que acepte las imagenes
                cache: false,
                processData: false,
                contentType: false,
                url: action_name,            
                success: function(data){
                        $("#userModal").modal("hide");
                        muestroMsg(data.ret,1000);
                        $("#id").val(data.id);                
                        searchByFormdata(); // Recargar la busq. o actualiza lo seleccionado
                },
                error:  function(xhr,err){ 
                   if (xhr.status == 401) {
              //        msgerror( "Se desconecto. Vuelva a Ingresar su Usuario");
                       document.location.reload(); // Para que recargue y pida login
                    }else{
                       var mensaje= "readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText;

                        $("#msgErrModal").html(mensaje);
                        $("#msgErrModal").show();

                    }

                } // Fin si hay error
            }); // Fin llamado Ajax

        }); // Fin de Aceptar el formulario de nuevo/modif.

    }); // Fin de document).ready


     function buscarMarcas( $familia, incluyeTodas, marca) {

        // global: false,   Hace que no despliegue msg de Procesando para este llamado

       // console.log ( incluyeTodas + $familia ) 

        $.ajax({
            global: false,
            dataType: "json",
            data: {"familia": $familia ,"marca": marca , "incluyeTodas": incluyeTodas },
            url:   '../marcas/combo_marca',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              if (incluyeTodas == 'S') {
                $("#filtroMarca").html(respuesta.html);
              } else {
                $("#Prod_Marca").html(respuesta.html);
              } 
            },
            error:  function(xhr,err){ 
               msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
            }
        });
    }

  function NuevoProducto() {

      var familia = $('#Prod_Familia').val();
      // Calcular Automaticamente proximo Id del Producto
      $.get('../productos/GeneroNvoCodigo?familia='+familia, {}, 'json')
            .done(function(data) {
              $('#Prod_Id').val(data.NvoCodigo);
              $('#Prod_Descripcion').focus();
      });

  }


  // Cuando cambia la Pestaña seleccionada  
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {

    var $table_auditoria = $('#tabla_auditoria'); 
    var $table_movi = $('#tabla_movi'); 
    var target = $(e.target).attr("href") // activated tab
    switch (target) {
      case '#movi': 
        $.ajax({
            dataType: "json",
            data: { familia: $('#Prod_Familia').val() , idprod: $('#Prod_Id').val()  },
            url:   '../{{$modulo_abm}}/lista_movimientos',
            type:  'get',
            success: function(data){
                $table_movi.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
        return;
      case '#auditoria': 
        $.ajax({
            dataType: "json",
            data: { familia: $('#Prod_Familia').val() , idprod: $('#Prod_Id').val()  },
            url:   '../{{$modulo_abm}}/lista_auditoria',
            type:  'get',
            success: function(data){
                $table_auditoria.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
        return;
      default:
        return;
    }
  });





</script>

@endsection()