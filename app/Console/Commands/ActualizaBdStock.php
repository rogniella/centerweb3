<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\Models\producto;
use App\Models\inventario;    

class ActualizaBdStock extends Command
{

    protected $signature = 'actualiza:ActualizaBdStock';

    // Ejecutar con: 
    // php artisan actualiza:ActualizaBdStock

    /**
        Proceso que toma archivo generado en sucursales, y lo compara con la Base Web
        Detecta diferencias de stock , categorias , etc.

        2022/06  - Se corrio y se arreglo tema de ñ en categorias.  Suc 1: 17.574 produc
                                                                    Suc 2:  4.353 produc
    */

    protected $description = 'Proceso de Actualizacion Bd Web - Actualiza Stock';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        // Se ejecutar Para Equiparar Stock Sucursal con la Base WEB
            
        $this->info('Proceso de Actualizacion Base Web - Toma Archivo Productos'); //Salida por Consola

        $cantidad_leidos = 0;

        $this->info("Inicio proceso archivo actualizacion Stock:");

        $file =  storage_path() . "/actualizacion/stock_1.json";
        $actualizar  = "no";
  
        if ( \File::exists($file) ) {
          $json = \File::get($file);
          $data = json_decode($json);
        } else {
          return view('mensaje', ['titulo' => "ERROR",
                                'mensaje' => "No se encontro el archivo json del lote, verifique archivo" . $file , 'pdf' => "", 'id' => "" ] );
        } 
  
        $cantidad = 0;
        $errores =0;
  
        $this->info( "Proceso Productos Sucursal: " . $data->sucursal . " Actualiza Datos:" . $actualizar );
    
        foreach ($data->productos as $row) {
  
            $cantidad ++;
            //$this->info(  "..Proceso " . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion );

            // Para procesar solo los de SOL
//            if ( TRIM ($row->Prod_Familia )!= 'SOL') {
//              continue;
//            }

            $modifico_producto = false;
            if  ( ! $oproducto  = Producto::findCodigo($row->Prod_Familia ,$row->Prod_Id ) ) {
                  $this->info(  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion . "  Error: No se encontro en tabla Productos Web");
                  $errores ++;
                  if  (  $actualizar == "ver" ) {
                      //lo doy de alta en la WEB
                      $oproducto = new producto( (array) $row);
                     // dd($oproducto);
                    //  $oproducto->Prod_Familia = $row->Prod_Familia;
                    //  $oproducto->Prod_Id = $row->Prod_Id;
                    //  $oproducto->Prod_Categoria = $row->Prod_Categoria;
                    //  $oproducto->Prod_Descripcion = $row->Prod_Descripcion;
                    //  $oproducto->Prod_CodBarra = $row->Prod_CodBarra ;
                      $oproducto->save();        
                  }else{
                    continue;
                  }    
            };
  
          // Actualiza el Stock 
          if (! $inventario = Inventario::findCodigo( $oproducto->Prod_idWEB ,$data->sucursal)) { 
            IF ($row->stock != 0 ) {
                $this->info(  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion . "  lo ingreso en tabla de Inventario Suc:" . $data->sucursal );
            }
            $inventario  = new Inventario;
            $inventario->Inv_idProd = $oproducto->Prod_idWEB;
            $inventario->Inv_Sucursal = $data->sucursal;
            $inventario->Inv_Stock = 0;
          }  
  
          if ( $inventario->Inv_Stock != $row->stock ) {
            $this->info (  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion . "  Error: No coincide Stock Web:" . $inventario->Inv_Stock . " Suc:" .  $row->stock ) ;
            $errores ++;
    
            if  (  $actualizar == "SI" ) {
              $inventario->Inv_Stock =  $row->stock;
              $this->info( '         Corrijo Stock:' . $row->Prod_Familia . ' ' . $row->Prod_Id );
              if ( ! $inventario->save() ) {
                $this->info(  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion .  " Error al actualizar stock en tabla Inventario " );
                $errores ++;
                continue;
              }      
            }  
          }
  
          if ( trim($oproducto->Prod_Descripcion) != trim($row->Prod_Descripcion)  ) {
            $this->info(  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion . "  Error: No coincide Descripcion En Web:" . $oproducto->Prod_Descripcion);
            $errores ++;
            $modifico_producto = true;
          }
  
          if ( trim($oproducto->Prod_Categoria) != trim($row->Prod_Categoria)  ) {
            $this->info(  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion . ' Cat:' . $row->Prod_Categoria . '  Error: No coincide Categoria En Web:' . $oproducto->Prod_Categoria);
            $errores ++;
            $modifico_producto = true;
          }
  
          if ( trim($oproducto->Prod_Estado) != trim($row->Prod_Estado)  ) {
             $this->info(  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion . "  Error: No coincide Estado En Web: " . $oproducto->Prod_Estado
              . " En Suc: " . $row->Prod_Estado );
            $errores ++;
            $modifico_producto = true;
          }
  
          // Corrijo llos datos de los productos en la WEB solo si es de la Sucursal 1 
          if ( $modifico_producto  and $actualizar == "ver" and  $data->sucursal == 1) {
              $this->info('  Corrijo Producto en WEB:' . $row->Prod_Familia . ' ' . $row->Prod_Id );
              $oproducto->fill( (array) $row);
              if ( ! $oproducto->save() ) {
                $this->info(  ".." . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion .  " Error al actualizar tabla Producto " );
                $errores ++;
                continue;
              }                
          }
        }   
  
        $this->info("Cantidad de Productos Procesados " . $cantidad . " Errores:" . $errores);

    }
}
