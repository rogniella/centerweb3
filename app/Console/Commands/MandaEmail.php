<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use Mail;  // Para el envio de mail

class MandaEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:Manda {--file=} {--asunto=} {--texto=}';


    // php artisan email:Manda --file=remito_01_4096.pdf --asunto="Este es el asunto"

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Proceso de Envio de Email';

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

        // Se ejecutar solo si se modifica alguna tabla de parametros
            
        $file_completo = $this->option('file', '');
      //$file_completo =  public_path() . "/remitos/" .  $file;
        $asunto = $this->option('asunto', '');

        $texto = $this->option('texto', '');

        displaylog("Proceso de Envio de Mail" . $asunto . '  Archivo:' . $file_completo );     
        $this->info('Proceso de Envio de Mail ' . $asunto . '  Archivo:' . $file_completo ); //Salida por Consola


    /**
        Entrada: - Id Remito Inter Sucursal
        Salida:  - Mail con archivo pdf
    **/

    $msgError = "Ok";

    // Mandar Mail
   // $asunto = "Remito Inter Sucursal  Nro: ";
   // $file =  "remito_01_4096.pdf";

    try {
      Mail::send('plantillaEmail',[ 'texto' => $texto] , function ($msj) use ($asunto,$file_completo) {
         $msj->to( env('SUCURSAL_ENVIO_MAIL') );
      //   $msj->cc('rogelio.niella@gmail.com');
         $msj->attach($file_completo, [
//                    'as' => $file,
                    'mime' => 'application/pdf',
                     ] )->subject( $asunto );
       });
    } catch (\Exception $e) {

        $msgError  = 'Error:No se pudo enviar email a la Sucursal:' . $e->getMessage();

    }      


        $this->info("     Envio     " . $msgError );



    }
}
