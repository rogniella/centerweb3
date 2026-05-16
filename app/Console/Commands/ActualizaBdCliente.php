<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\Models\cliente;

class ActualizaBdCliente extends Command
{

    protected $signature = 'actualiza:ActualizaBdCliente';

    // Ejecutar con: 
    // php artisan actualiza:ActualizaBdCliente

    /**
        Proceso que toma archivo generado en sucursales, y lo compara con la Base Web
        Detecta diferencias de telefono , pais

        2023/12  - Se corrio y se arreglo tema telefono.     Suc 1: nnn clientes
                                                             Suc 2: nnn clientes
    */

    protected $description = 'Proceso de Actualizacion Bd Web - Actualiza Cliente';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        // Se ejecutar Para Equiparar Stock Sucursal con la Base WEB
            
        $this->info('Proceso de Actualizacion Base Web - Toma Archivo Clientes'); //Salida por Consola

        $cantidad_leidos = 0;

        $file =  storage_path() . "/actualizacion/clientes_1.json";

        $this->info("Inicio proceso archivo actualizacion Stock:" .   $file  );

        $actualizar  = "no";
        //$actualizar  = "SI";
  
        if ( \File::exists($file) ) {
          $json = \File::get($file);
          $data = json_decode($json);
        } else {
            $this->info("No se encontro archivo:" .   $file  );
            dd ( "No se encontro archivo:" .   $file  );
        } 
  
        $cantidad = 0;
        $actualiza = 0;
        $errores =0;
  
        $this->info( "Proceso Clientes Sucursal: " . $data->sucursal . " Actualiza Datos:" . $actualizar );
    
        foreach ($data->clientes as $row) {
  
            $cantidad ++;
            //$this->info(  "..Proceso " . $row->Prod_Familia . ' ' . $row->Prod_Id . " "  . $row->Prod_Descripcion );

            // Para procesar solo los de SOL
//            if ( TRIM ($row->Prod_Familia )!= 'SOL') {
//              continue;
//            }

            $modifico = false;
            $idcli =  $row->Id + (  $data->sucursal  * 1000000 );
            if  ( ! Cliente::find_id(  $idcli) ) {
                  $this->info(  ".." . $idcli . " "  . $row->Cli_ApeNom . "  Error: No se encontro en tabla Clientes Web");
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
  
  
          if ( trim($cliente->Cli_Pais) != trim($row->Cli_Pais)  ) {
            $actualiza ++;
            $modifico = true;
          }
  
          if ( trim($cliente->Cli_Telefono) != trim($row->Cli_Telefono)  ) {
            $actualiza ++;
            $modifico = true;
          }
  
          if ( $modifico and $actualizar == "SI" ) {
              $cliente->fill( (array) $row);
              if ( ! $cliente->save() ) {
                $this->info(  ".." . $idcli . " "  . $row->Cli_ApeNom . "  Error: Al Actualizar tabla Clientes Web");
                $errores ++;
                continue;
              }                
          }
        }   
  
        $this->info("Cantidad de Clientes Procesados " . $cantidad . " Actualizados:" . $actualiza .  " Errores:" . $errores);

    }
}
