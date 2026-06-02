@extends('template.informes')
@section('titulo','Consulta de Saldo por Cuenta')
   
@section('contenido')

<?php 

    if ($_GET) {
        $sucursal = $_GET["sucursal"];
        $cod_cuenta= $_GET["cuenta"];
        $moneda = $_GET["moneda"];
    }else{
        $sucursal = 1;
        $cod_cuenta = '02';
        $moneda = 'P';      
    }    
    
?>
<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de Saldo por Cuenta</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="control-label">Sucursal:</label>
                    <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == $sucursal ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>    
                </div>      

                <div class="form-group">
                    <label class="control-label">Cuenta:</label>
                    <select id="cuenta" name="cuenta"  class="form-control"  data-live-search="true"> </select>
                </div>      

                <div class="form-group">
                    <label class="control-label">Moneda:</label>
                    <select id="moneda" name="moneda"  class="form-control"  data-live-search="true"> </select>
                </div>      

              <div class="form-group">
                <button type="button" class="btn btn-primary pull-right" id="form-search-btn" onclick="consultar()">Actualizar</button>
              </div>

            </div> <!-- Fin Panel BodyInfo -->
        </div> <!-- Fin Panel Info -->

     </div> <!-- Fin col -->
  </div>   <!-- /.Row -->

  <!-- Panel De la Tabla -->
  <div class="panel panel-success">     

  <!-- Segunda Fila de Informes -->
  <!-- Fila  Panel De Totales -->
  <div class="row">
        <div class="col-sm-12">
           <div class="alert alert-danger" role="alert" id="tituloTotal">...</div>
        </div> <!-- fin de col 12 -->   
  </div>   <!-- /.Row -->

            <div id="toolbar">
              <label>&nbsp</label>
              <button type="button" class="pull-right btn btn-default"
                id="form-search-btn" onclick="cierre()">
                <i class="glyphicon glyphicon-plus"></i> Generar Cierre 
              </button>
            </div>
          <table id="mitabla"
           data-toggle="table"
           data-toolbar="#toolbar"
           data-toolbar-align="right"
           data-search="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list=""      
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="fecha"  data-halign="center" data-align="center" data-sortable="true" data-sorter="dateSorter" >Fecha</th>
            <th data-field="hora"  data-halign="center" data-align="center" data-sortable="true">Hora</th>
            <th data-field="codigo" data-halign="center" data-align="left" data-sortable="true">C&oacute;digo </th>
            <th data-field="haber" data-halign="center" data-align="right" data-sortable="true" data-sorter="priceSorter">Haber</th>
            <th data-field="debe" data-halign="center" data-align="right" data-sortable="true"  data-sorter="priceSorter" >Debe</th>
            <th data-field="descri"> Detalle</th>
            <!--  <th>Hab/Desc</th> Col 6 Oculta  Cod_Haber_Descuento -->
          </tr>
          </thead>
       </table>
  
  </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->    


</form> 


@endsection <!-- Fin Contenido -->


@section('scrip')


<script>


   // Vbles Generales de Entreda
  var $saldo = 0;
  var $ultid = 0;

  // Si cambia la Sucursal
  $("#sucursal").on("change", cambioSucursal );
  $("#cuenta").on("change", cambioCuenta);

   
  function cambioSucursal (){
      // Carga combo cuentas y Codigos segun Sucursal seleccionada
      $sucursal = $("#sucursal").val();
      var cuenta = {!! $cod_cuenta !!};
      $.ajax({
            global: false,
            dataType: "json",
            data: {"sucursal": $sucursal,"cod_cuenta": cuenta},
            url:   '../cajas/combo_cuenta_sucursal',
            type:  'get',
            success: function(respuesta){
              //lo que se si el destino devuelve algo
              $("#cuenta").html(respuesta.html);
              cambioCuenta(); // Para que cargue l combo de monedas
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }   
            }
      });
  }

  function cambioCuenta(){
      // Carga combo moneda segun cuenta seleccionada
      // global: false,   Hace que no despliegue msg de Procesando para este llamado
      // console.log('buscamoneda' , $destino)
      $cuenta = $("#cuenta").val();
      var moneda = '{!! $moneda !!}';

      $.ajax({
            global: false,
            dataType: "json",
            data: {"cuenta": $cuenta,"moneda": moneda},
            url:   '../cajas/combo_moneda_cuenta',
            type:  'get',
            success: function(respuesta){
              $("#moneda").html(respuesta.html);
            },
            error:  function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( xhr.responseText);
                }   
            }
      });
  }


    $(document).ready(function(){
         // Tomo los datos de entrada
         cambioSucursal (); // Para que cargue l combo 
     //    consultar();
    });      


  
    var $table = $('#mitabla'); // Tabla principal

    //  Cambio el Combo de Agrupacion    
    $(function () {

   //     $('select[name="sucursal"]').change(function () {
   //          consultar();
    //    });    
    });
  

    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",   
            data: { sucursal: $('#sucursal').val() , cuenta: $('#cuenta').val(), moneda: $('#moneda').val()  },
            url:   'saldos-listar',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
                $saldo = data.saldo;
                $ultid = data.ultid;


            $('#tituloTotal').html('<b><div style="color:BLACK;"> S A L D O : $  ' +  formatearNumeroConSeparadorDeMiles( data.saldoDescri , cantDecMonto) + '  </div></b>');

            },
            error: function(xhr,err){ 
                if (xhr.readyState == 401) { // Si se desconecto
                   document.location.reload(); // Para que recargue y pida login
                }else{
                   msgerror( 'Al consultar:' + xhr.responseText);
                }    
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
     
    function cierre() {

        Swal.fire({
          title: 'Generar Cierre Consolidación de la Cuenta ?',
          text: "Esta Seguro de Cerrar consolidación de Cuenta!",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonText: 'Cancelar',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Si, Consolidar!'
        }).then((result) => {
          if (result.value) {

          $.ajax({
            global: false,
            dataType: "json",
            data: { sucursal: $('#sucursal').val() , cuenta: $('#cuenta').val(), moneda: $('#moneda').val(), saldo: $saldo , ultid: $ultid },
            url:   'guardar-cierre',
            type:  'get',
            success: function(data){
              if(data.msgError == '') {
                consultar();
              }else{                 
                msgerror( data.msgError);
              } //if data.err

            },
            error:  function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
          }); // Fin llamado Ajax



          } // Confirmo
        })
    }        

</script>
 

@endsection <!-- Fin scrip -->
