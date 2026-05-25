@extends('template.informes')
@section('titulo','Center')

@section('contenido')

<div class="row">
    <div class="col-lg-12">
        <div class="well" style="display: flex; align-items: center; gap: 15px; padding: 12px 18px; margin-bottom: 15px;">
            <img src="{{ asset('imagenes/logoCenter.jpg') }}" alt="Center" style="height: 50px;">
            <div>
                <strong>{{ Auth::user()->apellidonombre ?: Auth::user()->name }}</strong><br>
                <small>Sucursal: {{ Auth::user()->sucursal }} &nbsp;|&nbsp; {{ \Carbon\Carbon::now()->format('d/m/Y') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row shortcut-cards" style="margin-top: 10px;">
    @forelse($sortedShortcuts as $route => $shortcut)
        <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
            <a href="{{ route($route) }}" class="shortcut-card {{ $shortcut['color'] }}">
                <i class="fa {{ $shortcut['icon'] }}"></i>
                <span>{{ $shortcut['label'] }}</span>
            </a>
        </div>
    @empty
        <div class="col-lg-12">
            <div class="alert alert-info">No hay accesos directos disponibles para tu perfil.</div>
        </div>
    @endforelse
    <div class="col-lg-12" style="margin-bottom: 5px;">
        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#shortcutsModal" title="Configurar accesos directos">
            <i class="fa fa-cog"></i> Configurar accesos
        </button>
    </div>
</div>

<hr>

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
                </div>
              </div>
          </div>
          <div class="item">
              <img class="second-slide" src="{{ asset('imagenes/home2.jpg')}}" alt="Second slide">
              <div class="container">
                <div class="carousel-caption">
                </div>
              </div>
          </div>
          <div class="item">
              <img class="third-slide" src="{{ asset('imagenes/home3.jpg')}}" alt="Third slide">
            <div class="container">
                <div class="carousel-caption">
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

<!-- Modal Configuracion -->
<div class="modal fade" id="shortcutsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-cog"></i> Configurar Accesos Directos</h4>
            </div>
            <form id="shortcutsForm" method="POST" action="{{ route('home.shortcuts.save') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Seleccioná los accesos que querés ver en el inicio y ordenalos arrastrando.</p>
                    <ul class="list-group shortcut-sortable" id="shortcutSortable">
                        @foreach($sortedAllShortcuts as $route => $shortcut)
                            <li class="list-group-item shortcut-sortable-item" data-route="{{ $route }}">
                                <span class="shortcut-drag-handle"><i class="fa fa-bars"></i></span>
                                <label class="checkbox-inline" style="margin-left: 8px;">
                                    <input type="checkbox" class="shortcut-checkbox" value="{{ $route }}" {{ in_array($route, $userShortcuts) ? 'checked' : '' }}>
                                    <i class="fa {{ $shortcut['icon'] }}"></i> {{ $shortcut['label'] }}
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <input type="hidden" name="shortcuts" id="shortcutsValue">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scrip')

<link rel="stylesheet" href="{{ asset('plugins/AdminLTE/AdminLTE.css')}}">

<style>
.shortcut-cards {
    margin-bottom: 10px;
}
.shortcut-card {
    display: block;
    text-align: center;
    padding: 18px 8px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 13px;
    font-weight: bold;
    text-decoration: none;
    color: #fff;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.shortcut-card:hover,
.shortcut-card:focus {
    text-decoration: none;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.shortcut-card i {
    display: block;
    font-size: 28px;
    margin-bottom: 6px;
}
.card-sale  { background: #27ae60; }
.card-prod  { background: #2980b9; }
.card-client { background: #e67e22; }
.card-buy   { background: #16a085; }
.card-inv   { background: #8e44ad; }
.card-stock { background: #d35400; }
.card-opt   { background: #00aedb; }
.card-admin { background: #7f8c8d; }

.shortcut-sortable {
    min-height: 30px;
}
.shortcut-sortable-item {
    display: flex;
    align-items: center;
    cursor: default;
    padding: 8px 12px;
}
.shortcut-drag-handle {
    cursor: grab;
    color: #999;
    padding: 0 4px;
}
.shortcut-sortable-item .checkbox-inline {
    padding-top: 0;
}
.shortcut-sortable-item.ui-sortable-helper {
    background: #f5f5f5;
    border: 1px dashed #aaa;
}
</style>

<script>
$('#shortcutsForm').on('submit', function() {
    var ordered = [];
    $('#shortcutSortable .shortcut-sortable-item').each(function() {
        var $checkbox = $(this).find('.shortcut-checkbox');
        if ($checkbox.is(':checked')) {
            ordered.push($checkbox.val());
        }
    });
    $('#shortcutsValue').val(JSON.stringify(ordered));
});

$('#shortcutsModal').on('shown.bs.modal', function () {
    if (!$('#shortcutSortable').hasClass('ui-sortable')) {
        $('#shortcutSortable').sortable({
            handle: '.shortcut-drag-handle',
            placeholder: 'list-group-item shortcut-sortable-item',
            axis: 'y'
        });
    }
});
</script>

@endsection