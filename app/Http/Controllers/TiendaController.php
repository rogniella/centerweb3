<?php

namespace App\Http\Controllers;

use App\Producto;

use Illuminate\Http\Request;

class TiendaController extends Controller
{


    public function index()
    {

        $destacados =   [
          (object) [  'titulo' => 'Nueva Temporada',
          'subtitulo' => 'Primavera 2020 !!',
          'precio' => 10500,
          'precio2' => 9200,
          'imagen' => 'imagenes/destacados/17.jpeg'],

          (object) [  'titulo' => 'Nueva Temporada',
          'subtitulo' => 'Primavera 2!!',
          'precio' => 9900,
          'precio2' => 8200,
          'imagen' => 'imagenes/destacados/18.jpeg'],

          (object) [  'titulo' => 'Populares',
          'subtitulo' => 'Temporada',
          'precio' => 8500,
          'precio2' => 6500,
          'imagen' => 'imagenes/destacados/16.jpeg'],

        ];

        $categorias =   [
          (object) [  'titulo' => 'Receta',
          'clase' => '',
          'valor' => 'categoria/REC'],
          (object) [  'titulo' => 'Sol',
          'clase' => 'active',
          'valor' => 'categoria/SOL'],
          (object) [  'titulo' => 'Relojes',
          'clase' => '',
          'valor' => 'categoria/REL'],
          (object) [  'titulo' => 'Celulares',
          'clase' => '',
          'valor' => 'categoria/CEL'],
        ];


      $populares =   [
          (object) [  'titulo' => 'Nueva Temporada  pppp',
          'precio' => 1500,
          'imagen' => 'imagenes/productos/a.jpeg'],

          (object) [  'titulo' => 'Citizen',
          'precio' => 15.000,
          'imagen' => 'imagenes/productos/b.jpeg'],

          (object) [  'titulo' => 'Nueva Temporada',
          'precio' => 1500,
          'imagen' => 'imagenes/productos/c.jpeg'],
        ];

      $marcas =   [
          (object) [  'titulo' => 'Nueva Temporada',
          'precio' => 1500,
          'imagen' => 'imagenes/marcas/1200px-Ray-Ban_logo.svg.png'],

          (object) [  'titulo' => 'Citizen',
          'precio' => 15.000,
          'imagen' => 'imagenes/marcas/reef1.png'],

          (object) [  'titulo' => 'Nueva Temporada',
          'precio' => 1500,
          'imagen' => 'imagenes/marcas/4.jpg'],
          (object) [  'titulo' => 'Nueva Temporada',
          'precio' => 1500,
          'imagen' => 'imagenes/marcas/1474555_623530981040360_1959108186_n.jpg'],
          (object) [  'titulo' => 'Nueva Temporada',
          'precio' => 1500,
          'imagen' => 'imagenes/marcas/ossira-logo.jpg'],
          (object) [  'titulo' => 'Nueva Temporada',
          'precio' => 1500,
          'imagen' => 'imagenes/marcas/9.jpg'],
        ];

        $mas_visto = Producto::with('images')->where('Prod_Descripcion','like',"%hol%")->orderBy('Prod_Descripcion')->paginate(15);

        $mas_vendido = Producto::with('images')->where('Prod_Descripcion','like',"%citizen%")->orderBy('Prod_Descripcion')->paginate(15);


        return view('tienda.index', compact('destacados','categorias','populares','marcas','mas_vendido') );
    }

}
