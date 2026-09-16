@extends('template.main')

@section('titulo', 'Servicios')

@section('contenido')
 
<div class="row">
    <div class="col-sm-5">
        <div class="panel panel-info">         
            <div class="panel-heading">
                <h3 class="panel-title">INFORMACIÓN : <?= $datos['titulo']; ?></h3>
            </div>   <!-- /.panel-heading -->
            <div class="panel-body">                
                <div class="form-group">
	     		  <label class="control-label">Usuario:</label>
        		  <input type="text" class="form-control" id="usu" id="usu" value="<?= $datos['usuario']; ?>">
	     		  <label class="control-label">Clave:</label>
        		  <input type="text" class="form-control" id="cla" id="cla" value="<?= $datos['clave']; ?>">
	     		  <label class="control-label">Ruta:</label>
        		  <input type="text" class="form-control" id="ruta" id="name" value="<?= $datos['ruta']; ?>">
    		    </div>                
            </div>    <!-- /.panel-body -->
        </div>   <!-- / Fin.panel -->
    </div>  <!-- Fin .col-lg -->
</div>   <!-- /.Row -->

@endsection <!-- Fin Contenido -->

@section('scrip')

<script>
    
    $(document).ready(function(){
      ruta = $('#ruta').val();
      window.open(ruta, '', 'width=630,height=552,scrollbars=NO,statusbar=NO,left=400,top=100');
    });
        
</script>

@endsection <!-- Fin scrip -->
