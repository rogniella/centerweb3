@extends('template.informes')
@section('titulo','Cierre Mensual')
   
@section('contenido')

<form class="form-inline" role="form" >
 
  <!-- 1ra Fila de Informes -->
  <div class="row">
   <div class="col-sm-12">
        <!-- Panel Del Titulo y Filtros -->
        <div class="panel panel-info">         
            <div class="panel-heading">
              <h3 class="panel-title">Cierres Mensuales </h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="control-label">Período:</label>
                    <input type="number" class="form-control" name="periodo" id="periodo" 
                          value="<?= date("Ym"); ?>" required/>
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
           class="table table-striped"
          >
          <thead>
          <tr> 
            <th data-field="familia" data-halign="center" data-align="left" data-sortable="true">Rubro</th>
            <th data-field="descripcion" data-halign="center" data-align="left" data-sortable="true">Producto</th>
            <th data-field="stock_ini" data-field="MCaj_Moneda" data-halign="right" data-align="center" data-sortable="true">Stock Inicial</th>
            <th data-field="ventas" data-halign="center" data-align="right" data-sortable="true">Ventas</th>
            <th data-field="compras" data-halign="center" data-align="right" data-sortable="true">Compras</th>
            <th data-field="sucursales" data-halign="center" data-align="right" data-sortable="true">Sucursales</th>
            <th data-field="ajustes" data-halign="center" data-align="right" data-sortable="true">Ajustes</th>
            <th data-field="stock_resultado" data-halign="center" data-align="right" data-sortable="true">Stock Resultante</th>
            <th data-field="stock" data-halign="center" data-align="right" data-sortable="true">Stock Actual</th>
            <th data-field="reposicion" data-halign="center" data-align="right" data-sortable="true">Reposición</th>


            <th data-field="ventas_p" data-halign="center" data-align="right" data-sortable="true">Ventas $</th>
            <th data-field="compras_p" data-halign="center" data-align="right" data-sortable="true">Compras $</th>
            <!--  <th>Hab/Desc</th> Col 6 Oculta  Cod_Haber_Descuento -->
          </tr>
          </thead>
       </table>
  </div> <!-- fin Panel Tabla -->
    </div> <!-- fin de col 12 -->          
  </div>   <!-- /.Row -->

  <button type="button" class="btn btn-success pull-right" onClick="procesar()">ReProcesar Cierre</button>

</form> 


@endsection <!-- Fin Contenido -->


@section('scrip')


<script>


    $(document).ready(function(){
        // Se ejecuta al iniciar
        consultar();

    });      
  
    var $table = $('#mitabla'); // Tabla principal

    // Funcion de carga de Tablas
    function consultar()  {

       var $periodo = $("#periodo").val()
   
       $.ajax({
            dataType: "json",
            data: { periodo:$periodo},
            url:   'lista',
            type:  'get',
            success: function(data){
                $table.bootstrapTable('load', data.results);            
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }); // Fin llamado Ajax
    } // Fin consultar()
    

    function procesar()  {

     var $periodo = $("#periodo").val()

     Swal.fire({
          title: 'ReProcesar Cierre Mes ?',
          text: "Esta Seguro de Procesar Cierre de Mes, Se actualizaran datos!",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonText: 'Cancelar',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Si, Procesar!'
     }).then((result) => {
      if (result.value) {

       $.ajax({
            dataType: "json",
            data: { periodo:$periodo},
            url:   'proceso',
            type:  'get',
            success: function(data){
  //              $table.bootstrapTable('load', data.results);            
              Swal.fire(
               'Finalizado!',
               'Cierre Mes se Procesó con Éxito.' + '<br>' + data.mensaje,
               'success'
              )
            },
            error: function(xhr,err){ 
                msgerror( xhr.responseText);
            } // Fin si hay error
        }) // Fin llamado Ajax
      } 
     })

    } // Fin procesar()
    
</script>
 
@endsection <!-- Fin scrip -->
