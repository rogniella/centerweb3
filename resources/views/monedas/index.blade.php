<?php
    
  // Configurar las opciones de la Pagina ABM Modal

  $titulo='Administración de Monedas ';  

  $conf_crud = [
    'boton_consulta' =>  'N'
    ,'boton_opcion_extra' => '`<button type="button" class="btn btn-success btn-xs" title="ADM de Cotización" onclick="cotizacion(\'` +  value  +  `\', \'` + columnas.mon_descripcion  +  `\', \'` + columnas.cotizacion +   `\')"> <i class="glyphicon glyphicon-usd" aria-hidden="true"></i></button>`'
    ,'boton_opcion_extra2' => ''
  ]; 

  $campos_busqueda = [];

  $columnas_tabla = [
      [ 'titulo' => 'Moneda'    , 'tipo' => "data-field='mon_moneda' data-halign='center' data-align='center' data-sortable='true'"],
      [ 'titulo' => 'Descripción'     , 'tipo' => 'data-field="mon_descripcion" data-halign="center" data-align="left" data-sortable="true"'],
      [ 'titulo' => 'Cod.Númerico', 'tipo' => 'data-field="mon_codnum" data-halign="center" data-align="center" data-sortable="true"'],
      [ 'titulo' => 'Estado', 'tipo' => 'data-field="mon_estado" data-halign="center" data-align="center" data-sortable="false"'],
      [ 'titulo' => 'Cotización', 'tipo' => 'data-field="cotizacion" data-halign="center" data-align="center" data-sortable="false"'],
      [ 'titulo' => 'Última Cotización', 'tipo' => 'data-field="ultfeccot" data-halign="center" data-align="center" data-sortable="false"'],
      [ 'titulo' => 'Opciones', 'tipo' => 'data-field="mon_moneda" data-align="center" data-formatter="opcionesFormatter"']
  ];


  // Formulario de Alta/ Modificacion 
  $modulo_abm='monedas';  
  // Cuidado respetar minuscula y mayuscula como estan los nombres en la bd -->
  $campos_pantalla = [
      [ 'name' => 'Mon_Moneda'],
      [ 'name' => 'Mon_Descripcion'],
      [ 'name' => 'Mon_CodNum'],
      [ 'name' => 'Mon_Estado']
  ];

?>
  
@extends('template.abm_modal')

<!-- Formulario de Alta/ Modificacion -->
<!-- Cuidado respetar minuscula y mayuscula como estan los nombres en la bd -->

@section('primer_campo','Mon_Moneda')  <!-- Es el campo que se selecciona al abrir la ventana -->

@section('formulario_alta_modificacion')

    <div class="modal-body">
        <label>Moneda</label>
        <input type="text" class="form-control"  id="Mon_Moneda"  name="Mon_Moneda"  required title="Indentificación de la Moneda">
        <br>
        <label>Descripción</label>
        <input type="text" class="form-control"  id="Mon_Descripcion" name="Mon_Descripcion" required>
        <br>
        <label>Código Numérico</label>
        <input type="number" class="form-control"  id="Mon_CodNum" name="Mon_CodNum" required>
    </div> <!-- FIN Modal body -->

@endsection()
<!-- FIN Formulario de Alta/ Modificacion -->

@section('formulario_otros')

<!-- Formulario cotizaciones -->
<div id="userModal_cotizacion" class="modal fade" data-backdrop="static">
  <div class="modal-dialog modal-lg" >
    <form method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4> Mantenimieto de Cotizaciones </h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-3 col-md-3">
               <div id="msgMoneda" class="alert alert-success" align="left"  ></div>                
            </div>
            <div class="col-lg-4 col-md-4">
               <div id="msgCotizacion" class="alert alert-warning" align="left"  ></div>
            </div>
          </div> <!-- /Fin Row 2 Seleccion de Articulo -->
         <div class="row">
            <div class="col-lg-4 col-md-4">
                <label>Nueva Cotización:</label>
                <br>
                <input class="form-control text-right" type="number"  id="cotiza" value="">
            </div>

            <input type="hidden" id="moneda_id" name="moneda_id" >

          </div> <!-- /Fin Row 2 Seleccion de Articulo -->          
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
            <div id="msgErrorVentanaCotiza" class="alert alert-danger" align="left" hidden="true" ></div>

            <input type="button"  class="btn btn-success" onclick="registrar_cotizacion()"  value="Aceptar">
            <button type="button" class="btn btn-default" data-dismiss="modal">Regresar</button>
        </div>
      </div> <!-- Fin Modal Content -->
    </form>
  </div> <!-- Fin Modal Dialog -->
</div> <!-- FIN Formulario de Cotizacion -->

@endsection()
<!-- FIN Formulario Otros -->


@section('scrip_alta_modif')

<script>

function cotizacion($id , $descripcion,$cotizacion) {

$('#moneda_id').val($id);
$('#msgMoneda').html('Moneda:' + $descripcion  );
$('#msgCotizacion').html(  '    Cotización Actual:' + $cotizacion );
$('#cotiza').val('');
      

$("#userModal_cotizacion").modal("show")
    // Cuando termina de mostrarse, selecciono el 1re campo.
    .on("shown.bs.modal", function(e) {

      $("#cotiza").select(); 
});                    


}

function registrar_cotizacion(  )  {

  // Boton Aceptar de la Ventana Cotizacion

  $.ajax({
      global: false,
      dataType: "json",
      data: { 
               monedaid: $("#moneda_id").val()
              , cotiza: $("#cotiza").val()
          },
      url:   'graba_cotizacion',
      type:  'get',
      success: function(data){
            // Mens OK , y cerrar ventana modal
              $("#userModal_cotizacion").modal("hide");
              muestroMsg(data.msg,1000);
              searchByFormdata() ; // Recargar la busq.   

      },
      error:  function(xhr,err){ 
          msgerror( xhr.responseText);
      } // Fin si hay error
  }); // Fin llamado Ajax
} // Fin 
</script>
@endsection()
