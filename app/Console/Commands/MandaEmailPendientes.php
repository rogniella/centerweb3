<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;  // Para el envio de mail
use App\Models\correo;  

class MandaEmailPendientes extends Command
{


    protected $signature = 'email:MandaPendientes';


    // Se ejecuta automaticamente en tareas Cron en el servidor
    //   cd /home/gtgjotcc/centerweb/;php artisan email:MandaPendientes 1>/dev/null 2>&1
    // php artisan email:MandaPendientes

    // manda siempre 
    // 1>/dev/null 2>&1 | mail -s "Error envio Mail CenterWeb" r_niella@hotmail.com


    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Proceso de Envio de Email Pendientes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

       $this->info('Proceso Envio Mail Pendientes'); //Salida por Consola

       $cantidad = 0;
        // Se ejecutar para mandar todos los mail pendientes
       $correos = correo::where('estado', '=', '')->get();

       foreach ($correos as $row) {
          $this->info('    Envio: ' . $row->asunto); 
          $msgError = "Ok";
           try {
            $asunto = $row->asunto;
            $file =  $row->adjunto;
            //$destino = array ($row->destino);
            $v_destino = explode ( "," , $row->destino );
            $count = count($v_destino);
            $this->info("  Destinatarios:     " . $count );
            for ($i = 0; $i < $count; $i++) {
                $this->info("       " . $v_destino[$i] );
                $destino = $v_destino[$i];

             Mail::send('plantillaEmail',[ 'texto' => $row->texto] , function ($msj) use ($asunto,$file,$destino) {
                $msj->to( $destino );
                if ($file != '') {
                  $msj->attach($file, [
                    'mime' => 'application/pdf',
                     ] );                
                }
                $msj->subject( $asunto );
              });


            } // Fin for Destinatarios


              $row->estado = 'E'; // Enviado
              $cantidad = $cantidad + 1;
            } catch (\Exception $e) {
              $msgError  = 'Error:No se pudo enviar email:' . $e->getMessage();
              $row->msgError = $msgError;
              $this->error($msgError);
              displaylog("ERROR en Envios de Mails: " . $msgError );
              $asunto = "ERROR en Envios de Mails";
              $adm = 'r_niella@hotmail.com'; 
              Mail::send('plantillaEmail',[ 'texto' => $msgError] , function ($msj) use ($asunto,$file,$adm ) {
                 $msj->to( $adm );
                 $msj->subject( $asunto );
              });
            }
                  
            $row->save();
            $this->info("       " . $msgError );
       } // fin foreach

       if ($cantidad > 0 ) {
          displaylog("     Envios de Mails   Cantidad: " . $cantidad );     
          $this->info("      Envios de Mails   Cantidad: " . $cantidad ); //Salida por Consola
       }


    } // fin handle   

} // Fin de la Clase
