@extends('template.abm_modal')

<?php
    
  // Configurar las opciones de la Pagina Principal

  $titulo='Administración de Clientes';  

  $conf_crud = [
    'boton_consulta' =>  'S'
    ,'boton_opcion_extra' => ''
    ,'boton_opcion_extra2' => ''
  ];   

  $campos_busqueda = [
      [ 'name' => 'filtro_apenom', 'placeholder' => 'Apellido/Documento'],
  ];

  $columnas_tabla = [
      [ 'titulo' => 'Apellido y Nombre'    , 'tipo' => "data-field='cli_apenom' data-halign='center' data-align='left' data-sortable='true'"],
      [ 'titulo' => 'Tipo Doc', 'tipo' => 'data-field="cli_coddocumento" data-halign="center" data-align="center" data-sortable="true"'],
      [ 'titulo' => 'Documento/Cuit'     , 'tipo' => 'data-field="cli_documento" data-halign="center" data-align="center" data-sortable="true"'],
      [ 'titulo' => 'Teléfono', 'tipo' => 'data-field="cli_telefono" data-halign="center" data-align="left" data-sortable="true"'],
      [ 'titulo' => 'Sucursal', 'tipo' => 'data-field="cli_sucursal" data-halign="center" data-align="center" data-sortable="true"'],
      [ 'titulo' => 'Id Suc', 'tipo' => 'data-field="cli_id" data-halign="center" data-align="center" data-sortable="false"'],
      [ 'titulo' => 'Opciones', 'tipo' => 'data-field="cli_idWEB" data-align="center" data-formatter="opcionesFormatter"']
  ];

?>

@php
include base_path('resources/views/clientes/campos.php');
@endphp
@include('clientes.alta_modif')
