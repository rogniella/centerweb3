@extends('template.abm_modal')

<?php
    
  // Configurar las opciones de la Pagina Principal

  $titulo='Administración de Proveedores';  
  $conf_crud = [
    'boton_consulta' =>  'N',
    'boton_opcion_extra' => ''
    ,'boton_opcion_extra2' => ''
  ]; 

  $campos_busqueda = [
      [ 'name' => 'filtro_razsocial', 'placeholder' => 'Razón_Social'],
      [ 'name' => 'filtro_cuit', 'placeholder' => 'Cuit']
  ];

  $columnas_tabla = [
      [ 'titulo' => 'Id'    , 'tipo' => "data-field='prov_id' data-halign='center' data-align='center' data-sortable='true'"],
      [ 'titulo' => 'Razón Social', 'tipo' => 'data-field="prov_razsocial" data-halign="center" data-align="left" data-sortable="true"'],
      [ 'titulo' => 'Nombre Fantacia' , 'tipo' => 'data-field="prov_nomfant" data-halign="center" data-align="left" data-sortable="true"'],
      [ 'titulo' => 'Cuit', 'tipo' => 'data-field="prov_cuit" data-halign="center" data-align="center" data-sortable="true"'],
      [ 'titulo' => 'Teléfono', 'tipo' => 'data-field="prov_telefono" data-halign="center" data-align="left" data-sortable="false"'],
      [ 'titulo' => 'Tipo', 'tipo' => 'data-field="prov_tipoprov" data-halign="center" data-align="center" data-sortable="true"'],
      [ 'titulo' => 'Opciones', 'tipo' => 'data-field="prov_id" data-align="center" data-formatter="opcionesFormatter"']
  ];

  include( base_path() . "/resources/views/proveedores/campos.php");

  // Formulario de Alta/ Modificacion 
  
?>

@include('proveedores.alta_modif')
  