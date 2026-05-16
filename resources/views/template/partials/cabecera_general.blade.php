<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token ??? -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('imagenes/logo.ico')}}">
	<title>@yield('titulo','Center')</title>


  
	<link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.css')}}">
	<link rel="stylesheet" href="{{ asset('plugins/chosen/chosen.css')}}">
	<link rel="stylesheet" href="{{ asset('plugins/trumbowyg/ui/trumbowyg.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-select-1.12.4-dist/css/bootstrap-select.min.css') }}"> 

    <!-- Para tablas -->
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-table-1.14.2-dist/bootstrap-table.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ran.css') }}"> 


    <!-- glyphicon  son de Boostrap 3    https://www.w3schools.com/bootstrap/bootstrap_ref_comp_glyphs.asp -->
    <!-- Font Awesome Son fa .... Para los icons de botones Ver en: https://fontawesome.com/v4.7.0/icons/  -->
    <link rel="stylesheet" href="{{ asset('plugins/font-awesome/css/font-awesome.min.css')}}">

    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css')}}">

<!-- Select2  Ayuda en  https://select2.org/ -->
<link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="{{ asset('plugins/select2/select2-bootstrap.min.css')}}"> 


</head>

<body>
