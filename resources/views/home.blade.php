@extends('template.informes')
@section('titulo','Center')
   
@section('contenido')

<div class="row">
    <div class="col-lg-12">

                <table class="table table-bordered text-center">
                  <tr>
                    <td>

    <a href="{{ route('ot.consulta')}}" class="btn btn-success"><i class="glyphicon glyphicon-eye-open"></i>  Consulta Anteojos</a>

                    </td>
                  </tr>
                  <tr>
                    <td>

    <a href="{{ route('productos.consultaprecio')}}" class="btn btn-success"><i class="glyphicon glyphicon-usd"></i>  Consulta Precios</a>


                    </td>
                  </tr>

                </table>

    </div>
</div>  <!-- /.row -->



                            
 <!-- Carousel ================================================== -->
    <div id="myCarousel" class="carousel slide" data-ride="carousel">
    <!-- Indicators -->
      <ol class="carousel-indicators">
          <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
          <li data-target="#myCarousel" data-slide-to="1"></li>
          <li data-target="#myCarousel" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner" role="listbox">
          <div class="item active">
              <img class="first-slide" src="{{ asset('imagenes/home1.jpg')}}" alt="First slide">
              <div class="container">
                <div class="carousel-caption">
                  <!-- comentario -->
                </div>
              </div>
          </div>
          <div class="item">
              <img class="second-slide" src="{{ asset('imagenes/home2.jpg')}}" alt="Second slide">
              <div class="container">
                <div class="carousel-caption">
                  <!-- comentario -->
                </div>
              </div>
          </div>
          <div class="item">
              <img class="third-slide" src="{{ asset('imagenes/home3.jpg')}}" alt="Third slide">
            <div class="container">
                <div class="carousel-caption">
                  <!-- comentario -->
                </div>
            </div>
          </div>
        </div>
      <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
          <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
          <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
    </div><!-- /.carousel -->

</div> <!-- /.row -->

@endsection <!-- Fin Contenido -->

@section('scrip')

<!-- Theme style -->
<link rel="stylesheet" href="{{ asset('plugins/AdminLTE/AdminLTE.css')}}">

<script>

          
</script>
@endsection <!-- Fin scrip -->