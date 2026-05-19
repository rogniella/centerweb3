@extends('template.main_alta_modal')

@section('titulo','Nueva Venta')

@section('contenido')

<div id="msgErrAfip" class="alert alert-danger" align="left" style="display:none"></div>

<div class="panel panel-info">
    <div class="panel-heading">
        <b>NUEVA VENTA</b>
    </div>

    <div class="panel-body">
        <form id="formularioPrincipal" autocomplete="off" role="form" onkeypress="return event.keyCode != 13;">
            <div class="row">
                <div class="col-lg-2 col-md-2">
                    <label>Sucursal</label>
                    <select name="sucursal" id="sucursal" class="form-control" required>
                        @foreach($sucursales as $key => $value)
                            <option value="{{ $key }}" {{ $key == "" ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-2">
                    <label>Vendedor</label>
                    <select class="form-control" name="id_vendedor" id="id_vendedor" autofocus>
                        <?php $vendedor = ''; ?>
                        @include('common.combo_vendedor')
                    </select>
                </div>
                <div class="col-lg-2 col-md-2">
                    <label>Tipo Comp.</label>
                    <select class="form-control" name="id_tipo_cbte" id="id_tipo_cbte">
                        @include('common.combo_comprobante_fiscal')
                    </select>
                </div>
                <div class="col-lg-2 col-md-2">
                    <label>Fecha</label>
                    <input class="form-control" type="date" name="fecha" id="fecha" value="<?= date("Y-m-d"); ?>" readonly>
                </div>
                <div class="col-lg-4 col-md-4">
                    <label>Cliente</label>
                    <div class="input-group">
                        <input type="hidden" id="id_clienteweb" name="id_clienteweb" value="">
                        <input class="form-control" type="text" id="id_cliente" name="id_cliente" value="" autocomplete="off" placeholder="DNI/Apelido/Nombre">
                        <span class="input-group-btn">
                            <button type="button" class="btn" id="modif-cliente-btn" title="Consultar/Modificar" onclick="BtnModificaCliente()">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="btn" title="Nuevo" onclick="BtnNuevoCliente()">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row" style="padding: 1px;">
                <div class="col-lg-8 col-md-8"></div>
                <div class="col-lg-4 col-md-4">
                    <div class="alert-info" id="datos_cliente" hidden>
                        <b><span id="nombre_cliente">xxxxxxxx</span><br></b>
                        <span id="dni_cliente">nnnnnnnnnn</span><br>
                        <span id="telefono_cliente">nnnnnn</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="json_items" id="json_items" value="">
            <input type="hidden" name="json_pagos" id="json_pagos" value="">

            <br>

            <!-- Panel Selección de Artículos -->
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <b>Selección de Artículos</b>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-2 col-md-2">
                            <select class="form-control" id="id_familia">
                                <?php $FLIA_ID = "VAR"; ?>
                                @include('common.combo_familia')
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-2">
                            <div class="input-group">
                                <input class="form-control" type="text" id="id_producto" placeholder="Buscar Artículo">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3">
                            <input class="form-control" type="text" id="descrip_producto" disabled>
                        </div>
                        <div class="col-lg-1 col-md-1">
                            <input class="form-control text-right" type="text" id="cantidad" placeholder="Cantidad">
                        </div>
                        <div class="col-lg-2 col-md-2">
                            <div class="input-group">
                                <span class="input-group-addon">$</span>
                                <input class="form-control text-right" type="text" id="precio_unitario" placeholder="Precio Unit.">
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-1">
                            <div class="input-group">
                                <span class="input-group-addon">%</span>
                                <input class="form-control text-right" type="text" id="bonif_unitario" value="0" placeholder="Bonif">
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-1">
                            <button type="button" class="btn btn-primary" title="Agregar" onclick="ingresar_articulo()">
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Items -->
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h4>Su Venta</h4>
                    <table id="tbl-items" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Cantidad</th>
                                <th>Producto</th>
                                <th style="text-align: center;">Precio Unit.</th>
                                <th style="text-align: center;">Bonif</th>
                                <th style="text-align: center;">SubTotal</th>
                                <th style="text-align: center;"><span class="fa fa-wrench"></span></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Panel de Formas de Pago -->
            <div class="panel panel-success">
                <div class="panel-heading">
                    <b>Formas de Pago</b>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-3">
                            <select class="form-control" id="metodoPago">
                                <option value="" disabled selected>Seleccione un método</option>
                                <option value="P">Pesos</option>
                                <option value="T">Tarjeta Crédito</option>
                                <option value="TB">Transferencia Bancaria</option>
                                <option value="CC">Cuenta Corriente</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3" id="opcionesPago"></div>
                        <div class="col-lg-1 col-md-1">
                            <button type="button" id="addBtnPago" class="btn btn-primary" onclick="agregarMetodoPago()">
                                <i class="fa fa-plus"></i> Agregar
                            </button>
                        </div>
                        <div class="col-lg-5 col-md-5" id="resumenPagos"></div>
                    </div>
                    <div class="row" style="padding-top: 10px;">
                        <div class="col-lg-7 col-md-7">
                            <table id="tbl-pagos" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Método</th>
                                        <th>Detalle</th>
                                        <th>Monto</th>
                                        <th style="text-align: center;"><span class="fa fa-wrench"></span></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Observaciones y Total -->
            <h4>Observaciones</h4>
            <div class="form-group row">
                <div class="col-xs-5">
                    <textarea id="observaciones" name="observaciones" rows="5" cols="70" placeholder="Escriba sus observaciones aquí..."></textarea>
                </div>
                <div class="col-xs-7 text-right">
                    <div class="input-group" style="display: inline-flex; align-items: center;">
                        <strong style="font-size: 20px; margin-right: 30px;">Total:</strong>
                        <span class="input-group-addon" style="font-size: 20px; width: 50px;">$</span>
                        <input type="text" class="form-control text-right" style="font-size: 20px; width: 160px;" name="total" id="total" value="0" readonly>
                    </div>
                </div>
            </div>
    </div>

    <div class="panel-footer">
        <div class="form-group row">
            <div class="col-xs-3">
                <button type="button" id="PresuButton" class="btn btn-warning" onclick="FinalizaPresu()">PRESUPUESTAR</button>
            </div>
            <div class="col-xs-9">
                <button type="button" id="VentaButton" class="btn btn-success pull-right" onclick="FinalizaVenta()">CONFIRMAR VENTA</button>
            </div>
        </div>
    </div>

    </form>
</div>

<!-- Permite Alta/Modificación de Clientes -->
@php
include base_path('resources/views/clientes/campos.php');
@endphp
@include('clientes.alta_modif')

@endsection

@section('scrip')
<script src="{{ asset('js/ventas.js') }}"></script>
@endsection
