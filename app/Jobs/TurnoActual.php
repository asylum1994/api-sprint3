<?php

namespace App\Jobs;
use App\Models\Turno;
use App\Models\Juego;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TurnoActual implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $element;
    public function __construct(Juego $juego)
    {
        //
        $this->element = $juego; 
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        date_default_timezone_set('America/La_Paz');
        $fechaActual = now()->toDateString();

        $turnos = Turno::whereDate('fecha_inicio', '<=', $fechaActual)
        ->whereDate('fecha_fin', '>=', $fechaActual)
        ->where('id_juego',$this->element->id)
        ->where('ganador', 'pendiente')
        ->get();
        
        foreach ($turnos as $value) {
             echo "procesando turnos \n";
             procesarTurno::dispatch($value);
           // $this->info($turno);
            sleep(2);
        }
    }
}

