<?php
    
  // Configurar las opciones de la Pagina ABM Modal

  $titulo='Administración de Informes';  

  $conf_crud = [
     'boton_consulta' =>  'N'
    ,'boton_opcion_extra' => '`<button type="button" class="btn btn-success btn-xs" title="ADM de Códigos" onclick="define_codigos(\'` +  value  +  `\', \'` + columnas.inf_Descripcion +  `\', \'` + columnas.inf_tipo  +    `\')"> <i class="glyphicon glyphicon-wrench" aria-hidden="true"></i></button>`'
    ,'boton_opcion_extra2' => '`<button type="button" class="btn btn-success btn-xs" title="ADM de % Rendimiento" onclick="define_codigos2(\'` +  value  +  `\', \'` + columnas.inf_Descripcion  +   `\')"> <i class="glyphicon glyphicon-list-alt" aria-hidden="true"></i></button>`'
  ]; 

  $campos_busqueda = [];

  $columnas_tabla = [
      [ 'titulo' => 'Id'    , 'tipo' => "data-field='inf_idinforme' data-halign='center' data-align='center' data-sortable='true'"],
      [ 'titulo' => 'Descripción'     , 'tipo' => 'data-field="inf_Descripcion" data-halign="center" data-align="left" data-sortable="true"'],
      [ 'titulo' => 'Tipo'     , 'tipo' => 'data-field="infTipo_Descripcion" data-halign="center" data-align="left" data-sortable="true"'],
      [ 'titulo' => 'Opciones', 'tipo' => 'data-field="inf_idinforme" data-align="center" data-formatter="opcionesFormatter"'],
      [ 'titulo' => 'Tipo Oculto'     , 'tipo' => 'data-field="inf_tipo" data-visible="false"'],
  ];


  // Formulario de Alta/ Modificacion 
  $modulo_abm='minformes';  
  // Cuidado respetar minuscula y mayuscula como estan los nombres en la bd -->
  $campos_pantalla = [
      [ 'name' => 'inf_idinforme'],
      [ 'name' => 'inf_tipo'],
      [ 'name' => 'inf_Descripcion'],
  ];

?>
  
@extends('template.abm_modal')

<!-- Formulario de Alta/ Modificacion -->
<!-- Cuidado respetar minuscula y mayuscula como estan los nombres en la bd -->

@section('primer_campo','inf_tipo')  <!-- Es el campo que se selecciona al abrir la ventana -->

@section('formulario_alta_modificacion')

    <div class="modal-body">
        <label>Tipo</label>
        <select name="inf_tipo" id="inf_tipo" class="form-control" required>
                        @foreach($tipos as $key => $value)
                            <option value="{{ $key }}" {{ $key == 0 ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
        <br>
        <label>Descripción</label>
        <input type="text" class="form-control"  id="inf_Descripcion" name="inf_Descripcion" required>
        <br>
    </div> <!-- FIN Modal body -->

@endsection()
<!-- FIN Formulario de Alta/ Modificacion -->

@section('formulario_otros')

<!-- Formulario Lista Codigos del Informe Tipo 1 para Mantener los Codigos  -->
<div id="userModal_lista" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-lg" >
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 id="msgTitulo1" > Informe: Lista de Códigos </h4>
        </div>
        <div class="modal-body">
           <div class="col-lg-12 col-md-12">   
           <div class="row">
                <div class="input-group">
                    <span class="input-group-addon">Tipo Informe:</span>
                    <select id="tipo_inf1" name="tipo_inf1" class="form-control">                            
                    <?PHP  
                        $INF_TIPO =1;     
                        $INF_ID =19; // POr defecto Personalizado
                    ?>
                    @include('common.combo_informe')
                    </select>
                </div>  
          </div> <!-- /.row -->
          <br>
          <div class="row">
              <select name="codigos" id="codigos" class="form-control select2" multiple="multiple" data-placeholder="Selecione los Códigos"
                    style="width: 100%;">
              </select>
          </div> <!-- /.row -->
          <br>
          </div> <!-- fin Col -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <input type="button"  class="btn btn-success" onclick="registrar_codigos()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de Lista de Codigos -->

<!-- Formulario Lista Codigos del Informe Tipo 2 para Mantener los Codigos  -->
<div id="userModal_tipo2" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-lg" >
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 id="msgTitulo2" > Informe: Lista de Códigos </h4>
        </div>
        <div class="modal-body">
           <div class="col-lg-12 col-md-12">   
           <div class="row">
                <div class="input-group">
                    <span class="input-group-addon">Tipo Informe:</span>
                    <select id="tipo_inf2" name="tipo_inf2" class="form-control">                            
                    <?PHP  
                        $INF_TIPO =2;     
                    ?>
                    @include('common.combo_informe')
                    </select>
                </div>  
          </div> <!-- /.row -->
          <br>
          <div class="row">
          <label>Informe 1: </label>
           {!! Form::select('inf2_info1', $informes_tipo1 , 0, ['id' => 'inf2_info1', 'class' => 'form-control', 'required']) !!}
          <br>
          </div> <!-- /.row -->
          <div class="row">
          <label>Informe 2: </label>
           {!! Form::select('inf2_info2', $informes_tipo1 , 0, ['id' => 'inf2_info2', 'class' => 'form-control', 'required']) !!}
          <br>
          </div> <!-- /.row -->
          </div> <!-- fin Col -->
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <input type="button"  class="btn btn-success" onclick="registrar_tipo2()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de Lista de Codigos -->


<!-- Formulario Lista Codigos del Informe -->
<div id="userModal_lista_porcentaje" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-lg" >
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 id="msgMoneda" > Informe: Lista de Códigos % Rendimiento</h4>
        </div>
        <div class="modal-body">
        <div class="col-lg-12 col-md-12">   

        <div class="row">
                        <div class="input-group">
                          <span class="input-group-addon">Tipo Informe:</span>
                          <select id="tipo_inf2" name="tipo_inf2" class="form-control">                            
                           <?PHP  
                              $INF_TIPO =1;     
                              $INF_ID =19; // POr defecto Personalizado
                            ?>
                           @include('common.combo_informe')

                          </select>
                        </div>  
                    </div> <!-- /.row -->
                    <br>
                    <div class="row">
                       <div id="jsTabla"></div>
                    </div> <!-- /.row -->


          </div> <!-- fin Col -->






        </div> <!-- FIN Modal body -->
        <div class="modal-footer">

            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de Lista de % Rendimento-->

@endsection()
<!-- FIN Formulario Otros -->


@section('scrip_alta_modif')

<!-- http://js-grid.com/     https://github.com/tabalinas/jsgrid -->

<link type="text/css" rel="stylesheet" href="{{ asset('plugins/jsgrid/jsgrid.min.css')}}" />
<link type="text/css" rel="stylesheet" href="{{ asset('plugins/jsgrid/jsgrid-theme.min.css')}}" />

<script type="text/javascript" src="{{ asset('plugins/jsgrid/jsgrid.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jsgrid/i18n/jsgrid-es.js')}}"></script>




<script>

  // Se ejecuta al cargar la pagina  
  $(document).ready(function() {
         //Initialize Select2 Elements 
         $('.select2').select2({
             language: "es",
             placeholder: 'Seleccione los códigos'
         });

  });


    // Cuando elige el tipo de Informe Visualiza los Codigos
    $("#tipo_inf1").on("change", cargaCodigosTipo1 );


    function cargaCodigosTipo1(){
        cargaCodigosDif( $("#tipo_inf1").val() )
    }  

     function cargaCodigosDif(informe_sel){
        // Carga combo Codigos de Movimientos segun Informe seleccionado
        // global: false,   Hace que no despliegue msg de Procesando para este llamado
        $informe_sel = $("#tipo_inf1").val();
        $modal = $("#userModal_lista");
        $cod = $("#codigos");
        $.ajax({
                global: false,
                dataType: "json",
            data: {"informe": informe_sel},
            url:   '../estadisticas/combo_codmov_informe',
            type:  'get',
            success: function(respuesta){
                    //lo que se si el destino devuelve algo
                    $("#codigos").html(respuesta.html);
                    $('.select2').select2({theme: "bootstrap",language: "es"});
            },
            error:	function(xhr,err){ 
                     msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
            }
        });                   
    }     

function define_codigos($id , $descripcion , $tipo ) {
    // Llama al Formulario que corresponda segun tipo de Informe
    if( $tipo == 1 ) {
        $('#tipo_inf1').val($id);
        $('#msgTitulo1').html('Informe:' + $descripcion  );
        cargaCodigosDif( $("#tipo_inf1").val() )          
        $("#userModal_lista").modal("show")
          // Cuando termina de mostrarse, selecciono el 1re campo.
            .on("shown.bs.modal", function(e) {
            $("#codigos").select(); 
        });                    
    }else{
        // Tipo 2 
        $('#tipo_inf2').val($id);
        $('#msgTitulo2').html('Informe:' + $descripcion  );
        $.ajax({
                global: false,
                dataType: "json",
            data: {"id": $id},
            url: 'lee_Tipo2',
            type:  'get',
            success: function(respuesta){
                    //lo que se si el destino devuelve algo
                    console.log(respuesta)
                    $("#inf2_info1").val(respuesta.info1);
                    $("#inf2_info2").val(respuesta.info2);
                    $("#userModal_tipo2").modal("show")
                    // Cuando termina de mostrarse, selecciono el 1re campo.
                        .on("shown.bs.modal", function(e) {
                         $("#inf2_info1").select(); 
                    });                    

            },
            error:	function(xhr,err){ 
                     msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
            }
        });                   
    }

}


function define_codigos2($id , $descripcion) {

  // Para cargar % de Rendimientos
$('#tipo_inf2').val($id);
$('#msgMoneda').html('Tipo de Informe:' + $descripcion  ); 
// cargaCodigosDif(  $id )
grilla_codigos( $id )

$("#userModal_lista_porcentaje").modal("show")
   // Cuando termina de mostrarse, selecciono el 1re campo.
    .on("shown.bs.modal", function(e) {
    $("#codigos").select(); 
});                    

}

function registrar_codigos(  )  {

  // Boton Aceptar de la Ventana Codigos
  $.ajax({
    global: false,
    dataType: "json",
    data: { 
          tipoinf: $("#tipo_inf1").val()
        , codigos: $('#codigos').val()
    },
    url:   'graba_codigos',
    type:  'get',
    success: function(data){
          // Mens OK , y cerrar ventana modal
            $("#userModal_lista").modal("hide");
            muestroMsg(data.msg,1000);
            searchByFormdata() ; // Recargar la busq.   
    },
    error:  function(xhr,err){ 
        msgerror( xhr.responseText);
    } // Fin si hay error
}); // Fin llamado Ajax
};

function registrar_tipo2(  )  {

// Boton Aceptar de la Ventana Informes Tipo2
$.ajax({
  global: false,
  dataType: "json",
  data: { 
        id: $("#tipo_inf2").val()
      , inf_idInforme: $("#tipo_inf2").val()
      , inf_info1: $('#inf2_info1').val()
      , inf_info2: $('#inf2_info2').val()
  },
  url:   'update',
  type:  'get',
  success: function(data){
        // Mens OK , y cerrar ventana modal
          $("#userModal_tipo2").modal("hide");
          muestroMsg("Actualizando..",1000);
       //    searchByFormdata() ; // Recargar la busq.   
  },
  error:  function(xhr,err){ 
      msgerror( xhr.responseText);
  } // Fin si hay error
}); // Fin llamado Ajax
};

function grilla_codigos( informe_sel )  {

    // Ayuda de jsGrid  http://js-grid.com/
    jsGrid.locale("es"); // Idioma Español

    $("#jsTabla").jsGrid({
        height: "auto",
        width: "100%",
        editing: true,    // Permitir editar 
       // inserting: true,  // Para Insertar
        sorting: true,
        paging: true,
        pageSize: 100,
        autoload: true,
        controller: {
            loadData: function() {
                var d = $.Deferred();
                $.ajax({
                    global: false,
                    dataType: "json",
                    data: {"informe": informe_sel},
                    url:   '../estadisticas/codigos_codmov_informe',
                    type:  'get'
                    }).done(function(response) {
                        d.resolve(response.data);
                });
                return d.promise();
            },
            updateItem: function( item) {
                var d = $.Deferred();
                $idcompra = $("#idcompra").val();
                console.log('UpdateItem' ,  item);    
                $.ajax({
                    global: false,
                    dataType: "json",
                    data: { id: item.id
                          , rendimiento: item.infCod_Rendimiento
                    },
                    url:   'graba_rendimiento',
                    type:  'get'
                    }).done(function(response) {

                    $("#jsTabla").jsGrid("render").done(function() {
                        console.log("rendering completed and data loaded");
                    });
                      //  d.resolve(response.results);
                });
  //              return d.promise();
            }
        },

        fields: [
            { type: "control",width: 10 , deleteButton: false, visible: true ,   
                itemTemplate: function (_, item) {  
                  if (item.IsTotal)
                        return "";
                        return jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                }
            }, //Iconos de funciones
            { name: "id", type: "number" , visible: false},
            { title: "Código", name: "infCod_Codigo", type: "number",width: 30 , readOnly: true ,  align: "center"  },
            { title: "Descripción", name: "MCod_Descripcion", type: "text", readOnly: true  ,width: 70 },
            { title: "% Rendimiento", name: "infCod_Rendimiento", type: "text", width: 30 , validate: "required"  ,  align: "center"},
        ]
    });

  };

</script>
@endsection()
