@extends('template.informes')
@section('titulo','Administración de Comprobantes Emitidos AFIP')
   
@section('contenido')

<?php 
    // Inicio los parametros de Filtrado
    $fecha = date("Y-m-d");
    $fecha_fin = date("Y-m-d");
    if ($_GET) {
        $fecha = $_GET["fecha"];
        $fecha_fin = $_GET["fechafin"];
    }    
?>
<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Administración de Comprobantes Emitidos AFIP</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">

                  <select id="filtro0" name="filtro0" class="form-control"> 
                      <option value= '' selected>[Todos Puntos]</option>
                      <option value= '0001'>0001 - Impresora Fiscal</option>
                      <option value= '0002'>0002 - Manuales Libres</option>
                      <option value= '0003'>0003 - Página AFIP</option>
                      <option value= '0004'>0004 - PDF - WS AFIP</option>
                      <option value= '0005'>0005 - PDF - WS AFIP Mercedes</option>
                      <option value= '0006'>0006 - Manuales Mercedes</option>
                      <option value= '9999'>9999 - Con Error Pendientes</option>
                  </select>
                  <select id="filtro1" name="filtro1" class="form-control"> 
                      <option value= ''>[Todos Tipos]</option>
                      <option value= 'A'>Facturas A</option>
                      <option value= 'B'>Facturas B</option>
                      <option value= 'R'>Notas Crédito A</option>
                      <option value= 'S'>Notas Crédito B</option>
                  </select>

                  <input type="text" class="form-control" name="filtro2" id="filtro2"  placeholder="  Descripción" value="">

                    <div class="input-group">
                    <button type="button" class="btn btn-default pull-right" id="daterange-btn">
                      <span>
                        <i class="fa fa-calendar"></i> Rango de fecha
                      </span>
                        <i class="fa fa-caret-down"></i>
                    </button>              
                    </div>
              </div>

              <div class="form-group">
                <button type="button" class="btn btn-primary pull-right" id="form-search-btn" onclick="consultar()">Actualizar</button>
              </div>

            </div> <!-- Fin Panel BodyInfo -->
        </div> <!-- Fin Panel Info -->

        <!-- Panel De la Tabla -->
        <div class="panel panel-success">     
          <table id="mitabla"
           data-toggle="table"
           data-toolbar-align="right"
           data-search="true"
           data-show-export="true" 
           data-export-data-type="all"  
           data-show-print="true"
           data-cache = "false"
           data-pagination="true"
           data-page-size="40"
           data-page-list=""      
           data-show-footer="true"            
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="fecha" data-halign="center" data-align="center" data-footer-formatter="idTotal" data-sortable="true" >Fecha</th>
            <th data-field="Fac_Sucursal" data-halign="center" data-align="center"  data-sortable="true" >Suc</th>
            <th data-field="Fac_Comprobante"  data-sortable="true" data-halign="center" data-align="center" ></th>
            <th data-field="Fac_NroPuntoVta"  data-sortable="true"data-halign="center" data-align="center" ></th>
            <th data-field="Fac_NroFactura" data-halign="center" data-align="center" data-sortable="true">Número</th>
            <th data-field="Fac_RazonSocial" data-sortable="true"> Razón Social</th>
            <th data-field="Fac_Total" data-halign="center" data-align="right" data-footer-formatter="mtoFormatter"  data-sortable="true">Improte</th>
            <th data-field="Fac_Estado" data-halign="center" data-align="center" data-sortable="true">Estado</th>
            <th data-field="Fac_TipoOT" data-align="center" >Ot/Vta</th>
            <th data-field="Fac_IdOT" data-formatter="fotmatoColSel"   data-align="center" >Nro.</th>
            <th data-field="Fac_id" data-align="center" data-formatter="opcionesFormatter"></th>
          </tr>
          </thead>
       </table>
  </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->

</form> 

@include('common.modal_consulta')

@endsection <!-- Fin Contenido -->

@section('scrip')

<script src="{{ asset('js/consulta_comprobante.js') }}"></script>

<script>

   // Formatea linea de Totales de la Grilla
   function idTotal() {
     return 'T O T A L E S'
   }

   function mtoFormatter(data) {
    // Calculo el todal 
    var field = this.field
    return '$' +  numberFormat(  data.map(function (row) {
      // por ahora lo hago entero, con decimales me da error
        cantidad = parseInt(row['Fac_Total']) 
      //  console.log  ( row[field] , cantidad )
        return +  ( cantidad  )
      }).reduce(function (sum, i) {
        return sum + i
      }, 0)  )
   }


    function opcionesFormatter(value,row,index) {
        var Id = value;
        var Tipo = "'"  + row.Fac_TipoOT + "'"
        var IdOt = "'"  + row.Fac_IdOT + "'"
        var Estado = "'"  + row.Fac_Estado + "'"  
        var PuntoVenta = row.Fac_NroPuntoVta 
        var Sucursal = "'"  + row.Fac_Sucursal + "'"

        var botones =""
       // console.log(row)

        if (PuntoVenta == '0003' || PuntoVenta == '0004' || PuntoVenta == '0005') {
           botones = '<button type="button" class="btn   btn-danger   btn-xs"'+
                  'title="Imprimir PDF" onclick="imprimePDF('+ Sucursal + ',' + Tipo + ',' + IdOt + ',' + Estado  +')">'+
                    '<i class="fa fa-file-pdf-o"></i>'+
                '</button>' + '&nbsp;'+
                '<button type="button" class="btn btn-success btn-xs"'+
                  'title="Consulta AFIP" onclick="consultaAFIP('+ Sucursal + ',' + Tipo + ',' + IdOt +')">'+
                    '<i class="fa fa-info-circle"></i>'+
                '</button>'; 
        }

        
        if (PuntoVenta == '9999') {
           botones = '<button type="button" class="btn   btn-warning   btn-xs"'+
                  'title="Generar Afip" onclick="imprimePDF('+ Sucursal + ',' + Tipo + ',' + IdOt + ',' + Estado +')">'+
                    '<i class="fa fa-external-link"></i>'+
                '</button>' + '&nbsp;'; 
            // Opcion Eliminar  Solo si esta conectado y es ADM -->
            @if(Auth::user())
              @if(Auth::user()->perfil_id == 'ADM')
                botones =  botones +
                    '<button type="button" class="btn btn-danger btn-xs"'+
                        'title="Eliminar registro" onclick="deleteReg('+  Id +')">'+
                        '<i class="glyphicon glyphicon-trash" aria-hidden="true"></i>'+
                    '</button>';
              @endif
            @endif

        }

        
        return botones; 
    }


    var $fecha ;
    var $fechafin;

    $(document).ready(function(){
        // Tomo los datos de entrada
         $fecha = '<?= $fecha; ?>';
         $fechafin = '<?= $fecha_fin; ?>';
         consultar();
    });      

    $('#daterange-btn').daterangepicker(
     {
      ranges   : {
        'Hoy'       : [moment(), moment()],
        'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
        'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
        'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
     },
     startDate: moment(),
     endDate  : moment()
     },
     function (start, end) {
        $('#daterange-btn span').html(start.format('D MMMM YYYY') + ' al ' + end.format('D  MMMM YYYY'))

        $fecha = start.format('YYYY-M-D');
        $fechafin = end.format('YYYY-M-D');
        consultar();
     }
  
    );  // Fin $('#daterange-btn').daterangepicker(

    var $table = $('#mitabla'); // Tabla principal

  // Todos los Eventos de la Tabla
  $table.on('all.bs.table', function (e, name, args) {

if (name == 'click-cell.bs.table' ) {   // Evento Click en un elemento de la tabla

  if ( args [0] == 'Fac_IdOT'){  // Nombre Columna                          
     // Busco los datos de la OT o Comprobante y despliega pantall Modal
     consulta_comprobante(args [2].Fac_TipoOT, args [2].Fac_IdOT, args [2].Fac_Sucursal)
  }; // Fin Clik Id OT

} // Clik de La tabla    

}); // Fin Todos los Eventos de la Tabla




    // Funcion de carga de Tablas
    function consultar()  {
       // LLama a 2da pagina con la logica de la busqueda
       // ------------------------------------------------      
       $.ajax({
            dataType: "json",
            data: { filtro0: $('#filtro0').val(), filtro1: $('#filtro1').val(), filtro2: $('#filtro2').val()  ,fecha: $fecha , fechafin: $fechafin  },
            url:   'buscar',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
    
    function imprimePDF( sucursal, tipo , Id , Estado)  {
 
      if(Estado == 'K') {
          ruta = 'generaComprobanteAFIP'
      }else{
          ruta = 'imprimePDF'
      }  

      $.ajax({
            dataType: "json",
            data: { sucursal: sucursal, tipo: tipo, id: Id, soloGenera:'si' },
            url:   '../ventas/' + ruta,
            type:  'get',
            success: function(data){
              if(data.retError == "" || data.retError == null) { 
                  // ir a la pantalla del pdf
                  if(data.pdf != "" ) {
                      window.open(data.pdf, '', '_blanck');
                      if(Estado == 'K') {
                         consultar(); // , ya que hay 1 error menos
                      }       
                  }    
              }else{
                  msgerror( data.retError);
              }
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } 

    function consultaAFIP( sucursal,tipo , Id)  {

       $.ajax({
            dataType: "json",
            data: { sucursal: sucursal, tipo: tipo, id: Id},
            url:   '../afip/consulta_factura',
            type:  'get',
            success: function(data){
               muestroMsg( data.msgError);         
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } 

    function deleteReg(  Id)  {

       Swal.fire({
                  title: 'Eliminar Comprobante Fiscal ?' ,
                  text: "Esta Seguro de Eliminar esta Factura!",
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonText: 'Cancelar',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Si, Eliminar!'
        }).then((result) => {
          if (result.value) {
           $.ajax({
            dataType: "json",
            data: { id: Id},
            url:   'delete',
            type:  'get',
            success: function(data){
                muestroMsg(data.ret,1000);
                consultar(); // Recargar la busq.    
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
          }); // Fin llamado Ajax
        }
      })  // Confirmacion              
    }  // deleteReg
  
</script>
 
@endsection <!-- Fin scrip -->
