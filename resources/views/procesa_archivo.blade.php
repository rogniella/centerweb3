@extends('template.main')
   
@section('contenido')

<form enctype="multipart/form-data" method="post" action="{{ $accion }}">
  @csrf
  <div class="row">
    <div class="col-sm-6 col-md-6">
      <div class="modal-content ">
        <div class="modal-header">
            <h4 id="modal-title">{{ $titulo }}  </h4>
        </div>
        <div class="modal-body">
          <br>   
          <div class="panel panel-success">         
              <div class="panel-heading">
                <b> {{ $mensaje }} </b>
              </div>
              <div class="panel-body">
               <div class="form-group">
                  <input type="file" id="nombre_archivo" name="nombre_archivo" accept="{{ $tipoArchivo }}" required/>
              </div>

               <div class="form-group">
                    <select name="actualiza" id="actualiza" class="form-control">
                        <option value="NO" >Simulasión</option>
                        <option value="SI" >Actualizar Datos</option>
                    </select>
                 </div> <!-- Fin row -->


              </div> <!-- Fin body Panel -->
          </div> <!-- Fin Panel -->
          <br>   
        </div> <!-- FIN Modal body -->
        <div class="modal-footer">
          <input type="submit" value="Procesar Archivo" class="btn btn-success pull-right" >
        </div> <!-- modal-footer -->
      </div> <!-- Fin Modal Content -->
    </div> <!-- fin col -->
  </div> <!-- fin row -->
</form> <!-- Fin Form --> 

@endsection <!-- Fin Contenido -->

