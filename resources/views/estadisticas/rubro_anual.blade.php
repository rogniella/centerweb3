@extends('template.informes')
@section('titulo','Estadisticas Por Rubros - Anual')

@section('contenido')

<?php 
    $anio = date("Y");  
    if (request()) {
        $sucursal = request()["sucursal"];
        $INF_ID = request()["tipoinf"];
        $id_producto = request()["id_producto"];
        $desc_producto = request()["desc_producto"];
        $anio_desde = request()["anio_desde"];
        $anio_hasta = request()["anio_hasta"];
    }else{
        $sucursal = "";
        $INF_ID = "REC";
        $id_producto = "";
        $desc_producto = "";
        $anio_desde = $anio - 5;
        $anio_hasta = $anio;
    }    
?>    
       
<form autocomplete="off" class="form-inline" role="form" >

    <div class="panel panel-info">
        <div class="panel-heading">      
          <div class="row">
            <div data-dismiss="alert" aria-hidden="true"></div>
            &nbsp<big><i class="fa fa-bar-chart"></i> <strong>Estadísticas Anuales por Rubros</strong></big>
          </div>    <!-- /.row -->
        </div> <!-- Fin panel-heading -->
        <div  class="panel-body">
            <div class="row" style="padding:2px;">

                        <div class="input-group">
                          <span class="input-group-addon">Sucursal:</span>
                <select name="sucursal_inf1" id="sucursal_inf1" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                        </div>

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Desde Año:</span>
                          <input type="number" class="form-control" id="anio_desde" value="<?= $anio_desde ?>" maxlength="4"
                     required/>
                        </div>
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Hasta Año:</span>
                          <input type="number" class="form-control" id="anio_hasta" value="<?= $anio_hasta ?>" maxlength="4"
                     required/>
                        </div>

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Rubro:</span>
                          <select name="tipo_inf1" id="tipo_inf1" class="form-control">
                        @foreach($familias as $key => $value)
                            <option value="{{ $key }}" {{ $key == $INF_ID ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                        </div>  

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Operación:</span>
                          <select id="operacion_inf1" name="operacion_inf1" class="form-control"> 
                            <option value= 'V' selected> Ventas</option>
                            <option value= 'C'> Compras</option>
                          </select>
                        </div>  

                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Expresado en:</span>
                          <select id="moneda" name="moneda" class="form-control">
                            <option value="1">Pesos</option>
                            <option value="2">Dolar Blue</option>
                          </select>
                        </div>

                        <div class="input-group" style="padding:2px;">
                            <a id="esconder" onclick="mostrar()">Mas filtros..</a>
                        </div>  

                    </div> <!-- Fin div row -->

                    <div id="ocultar" class="row" style="padding:1px;background:white;">
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Descripción Articulo:</span>
                            <input class="form-control" type="text" name="" value="<?= $desc_producto ; ?>" id="desc_producto" placeholder="Buscar Descripción Articulo">
                        </div>
                        <div class="input-group" style="padding:2px;">
                          <span class="input-group-addon">Articulo:</span>
                            <input class="form-control" type="text" value="<?= $id_producto ; ?>" name="" id="id_producto" placeholder="Buscar Articulo">
                        </div>
                        <label id="descrip_producto"> </label>

                    </div> <!-- Fin div oculto -->

                    <div class="row" style="padding:4px;">
                        <button type="button" id="btnconsulta" onClick="informe_por_tipo()" class="btn btn-primary pull-right">Consultar</button>
                    </div> <!-- /.row -->            

          </div> <!-- Fin panel body  Titulo --> 
        </div>  <!-- Fin panel Titulo --> 
    
    <!-- Primera Fila de Informes -->
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row" style="padding:2px;">
                        &nbsp<big><i class="fa fa-bar-chart"></i><strong> Cantidades por Año </big> </strong>  
                    </div> <!-- /.row -->
                </div> <!-- Fin panel-heading -->
                <div class="panel-body">
                          
                    <div id="morris-bar-inf1"></div>
                      <table id="tabla_inf1"
                       data-toggle="table"
                       data-cache = "false"
                       data-page-list=""

                       class="table table-striped"
                      >
                      <thead>
                      <tr>
                        <th data-field="periodo" data-halign="center"  data-align="center">Año</th>
                        <th data-field="valor1" data-halign="center" data-align="right">Cantidad</th>
                      </tr>
                      </thead>
                     </table>
                </div>  <!-- /.panel-body -->
            </div>    <!-- /.panel -->
        </div>   <!-- /.Col -->             

        <div class="col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row" style="padding:2px;">
                        &nbsp<big><i class="fa fa-usd"></i><strong> Montos por Año </big> </strong>  
                    </div> <!-- /.row -->
                </div> <!-- Fin panel-heading -->
                <div class="panel-body">
                          
                    <div id="morris-bar-inf2"></div>
                      <table id="tabla_inf2"
                       data-toggle="table"
                       data-cache = "false"
                       data-page-list=""

                       class="table table-striped"
                      >
                      <thead>
                      <tr>
                        <th data-field="periodo" data-halign="center"  data-align="center">Año</th>
                        <th data-field="valor1" data-halign="center" data-align="right">Monto</th>
                      </tr>
                      </thead>
                     </table>
                </div>  <!-- /.panel-body -->
            </div>    <!-- /.panel -->
        </div>   <!-- /.Col -->             

    </div>   <!-- /.Row -->
        

                
</form> 

@endsection <!-- Fin Contenido -->

@section('scrip')

              

<script>

  // Se ejecuta al cargar la pagina  
  $(document).ready(function() {

  <?php 
      if (request()) {
        echo "informe_por_tipo()";
      }
  ?>    
    

  });
        
      //funcion para ocultar el panel de filtrado
  $("#ocultar").hide();

  function mostrar(){
    let text="";
    if ($("#esconder").text() === 'Mas filtros..') {
      $("#ocultar").show();
      text = "Cerrar Filtros Extras";
    }else {
      $("#ocultar").hide();
      text = 'Mas filtros..'; // Abrir
    }
    $("#esconder").html(text);
  }


        // Busqueda Automatica de Productos
        $('#id_producto').typeahead({
            items: 15,
            minLength: 2,
            highlight: true,
            source: function(query, process) {
              var familia = $('#tipo_inf1').val();
              $.ajax({
                  global: false,
                  dataType: "json",
                  data: {},
                  url:   '../productos/buscaproducto?terms='+query+'&familia='+familia,
                  type:  'get',
                  success: function(data){
                     return process(data);
                  },
                  error:  function(xhr,err){ 
                    msgerror("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
                  }
              });
            },
            // Al seleccionar.
            afterSelect: function(item) {
                $('#id_producto').val(item.id);
                $("#descrip_producto").html(item.descripcion);

            }
        }); //Fin Busqueda Producto




  //  Para elegir color en Google poner "elegir color"
  //  -------------------------

  // Grafico Cantidad por Año 
  var grafico_inf1 = new Morris.Bar({
    element          : 'morris-bar-inf1',
    resize           : true,
    data             : [],
    xkey             : 'y',
    ykeys            : ['a'],
    labels           : ['Cantidad'],
    barColors: ['#1E25B2'],
    lineWidth        : 2,
    parseTime : false,
    hoverCallback: function (index, options, content, row) {
     return "<b>Año " + row.y + "</b><br>" + 
            "<b><div class='ran-azul'>Cantidad: </b> " + numberFormat(row.a) + "</div>";
    },    
    gridTextSize     : 10
  });


  // Grafico Monto por Año 
  var grafico_inf2 = new Morris.Bar({
    element          : 'morris-bar-inf2',
    resize           : true,
    data             : [],
    xkey             : 'y',
    ykeys            : ['a'],
    labels           : ['Monto'],
    barColors: ['#1EB236'],
    lineWidth        : 2,
    parseTime : false,
    hoverCallback: function (index, options, content, row) {
     var moneda = $('#moneda').val() == '2' ? 'USD' : '$';
     return "<b>Año " + row.y + "</b><br>" + 
            "<b><div class='ran-verde'>Monto: </b> " + moneda + " " + numberFormat(row.a) + "</div>";
    },    
    preUnits         : '$',
    gridTextSize     : 10
  });


  // Boton Consultar -> Trae los datos por Ajax
  function informe_por_tipo() {
        var action_name = "informe_rubro_anual_cant";
        var formdata = {
              sucursal: $('#sucursal_inf1').val(),
              tipo_inf: $('#tipo_inf1').val(),
              id_producto: $('#id_producto').val(),
              desc_producto: $('#desc_producto').val(),
              operacion: $('#operacion_inf1').val(),
              anio_desde: $('#anio_desde').val(),
              anio_hasta: $('#anio_hasta').val(),
              moneda: $('#moneda').val()
        }
        var $table = $('#tabla_inf1'); 
        $.get('rubro_anual_proceso?action=' + action_name, formdata, "json")
                .done(function(data) {
                     grafico_inf1.setData(data.grafico_cant);
                     $table.bootstrapTable('load', data.tabla_cant);
                     grafico_inf2.setData(data.grafico);
                     $tableinf2.bootstrapTable('load', data.tabla);
                })
                .fail(function(xhr,err) {                   
                    msgerror( xhr.responseText);
        });        
  }; // Fin Informe por tipo
  

  // Llamada Informe  por movimientos detallados
  var $tableinf2 = $('#tabla_inf2');
  $tableinf2.on('all.bs.table', function (e, name, args) {
        if (name == 'click-cell.bs.table' ) {
            if( args [2].periodo > 0 ) {
                ruta = '../productos/movimientos?cod_cero=S&sucursal=' + $('#sucursal_inf1').val() + '&id_producto=' + $('#id_producto').val() +  '&desc_producto=' + $('#desc_producto').val() + '&anio=' + args [2].periodo + '&familia=' + $('#tipo_inf1').val() + '&operacion=' +  $('#operacion_inf1').val() 
                window.open(ruta, '', '_blanck');
            };
        }    
   });

</script>
  
@endsection <!-- Fin scrip -->
