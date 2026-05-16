<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    //Ran  estaba en vs anterior
    protected $commands = [
        'App\Console\Commands\ActualizaBdCliente',
        'App\Console\Commands\ActualizaBdStock',
        'App\Console\Commands\MandaEmailPendientes',
        'App\Console\Commands\MandaEmail'
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
