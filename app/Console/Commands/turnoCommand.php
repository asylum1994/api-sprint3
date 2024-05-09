<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Turno;
use App\Jobs\procesarTurno;
use Illuminate\Support\Facades\Queue;

class turnoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:turno-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'procesar cada turno';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info("ejecutando comando turno..");
        date_default_timezone_set('America/La_Paz');
        $fechaActual = now()->toDateString();

        $turnos = Turno::whereDate('fecha_inicio', '<=', $fechaActual)
               ->whereDate('fecha_fin', '>=', $fechaActual)
               ->get();
        
        foreach ($turnos as $turno) {
             procesarTurno::dispatch($turno);
           // $this->info($turno);
            sleep(2);
        }
    }

}
