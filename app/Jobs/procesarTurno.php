<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\Models\Turno;
use App\Models\Juego;
use App\Models\User;
use App\Models\Participa;
use App\Models\JuegoTurno;
use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class procesarTurno implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $element;

    public function __construct(Turno $turno)
    {
        //
        $this->element=$turno; 
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        echo "ingresando al turno {$this->element->nro_turno} \n ";
        date_default_timezone_set('America/La_Paz');
        
        $fechaActual = now()->toDateString();
        

      // $juego=Juego::where('id',$this->element->id_juego)
      // ->where('estado_juego','finalizado')->first();

       $nodoJuego = Juego::where('id',$this->element->id_juego)->first();

       $turnoActual = Turno::where('id_juego',$this->element->id_juego)
       ->where('nro_turno',$this->element->nro_turno)->first();

        if ($fechaActual>=$turnoActual->fecha_ini && $fechaActual<=$turnoActual->fecha_fin){
              echo "ingreso al primer if \n";
           
            if ($turnoActual->ganador=='pendiente'){
                  echo "ingreasando al 2do if \n";
                   $participas = Participa::where('id_juego',$this->element->id_juego)
                   ->where('turno',null)
                   ->select('email')
                   ->get();
               
                   $listaToken = User::whereIn('email', $participas)->get(); 
             
                   foreach ($listaToken as $value) {
                   $this->sendNotification("Hola!! $value->email ","el turno # {$this->element->nro_turno} , en el juego {$nodoJuego->nombre} acaba de empezar, tienes 5 min para postular y ser el ganador del turno, ingresa a la app !!",$value->token);
                   }

                       echo "esperando...";
                       sleep(70);

                       $postulacionMaxima = Postulacion::where('id_juego', $this->element->id_juego)
                       ->where('turno',$this->element->nro_turno)
                       ->select('id_juego','postulante', 'monto','turno')
                       ->orderByDesc('monto')
                       ->first();
                       
                       $postulacionGanador = ""; 
                       $postulacionGanadorMonto = 0; 
                       if ($postulacionMaxima==null){
                          $postulacionAleatoria = Participa::where('id_juego',$this->element->id_juego)
                          ->where('turno',null)
                          ->inRandomOrder()->first(); 
                          $postulacionGanador=$postulacionAleatoria->email; 
                       }else{
                        $postulacionGanador=$postulacionMaxima->postulante; 
                        $postulacionGanadorMonto = $postulacionMaxima->monto; 
                       }

                       Turno::where('id', $this->element->id)->update(['ganador' => $postulacionGanador,'monto'=>$postulacionGanadorMonto]);
                       
                       $registros = Participa::where('estado_participa', 'aceptado')
                       ->where('id_juego',$this->element->id_juego)
                       ->where('email', '!=',$postulacionGanador)
                       ->get();
                       
                       $totalParticipantes = Participa::where('estado_participa', 'aceptado')
                           ->where('id_juego', $this->element->id_juego)
                           ->where('email', '!=',$postulacionGanador)
                           ->count();

                        ////////  descuento por turno
                        $cuotaTurno = 0;
                        if ($postulacionMaxima!=null){
                        echo "total participantes : $totalParticipantes \n";
                        $descuentoGanador=$postulacionMaxima->monto/$totalParticipantes;
                        echo "descuento : $descuentoGanador \n"; 
                        $cuotaTurno=($nodoJuego->monto/$totalParticipantes)-$descuentoGanador; 
                        echo "cuota a pagar: $cuotaTurno";
                        }else{
                          $cuotaTurno=$nodoJuego->monto/$totalParticipantes;  
                        } 

                       foreach($registros as $data){
                        $jugadorTurno = new JuegoTurno();
                        $jugadorTurno->id_turno = $this->element->id;
                        $jugadorTurno->jugador = $data->email;
                        $jugadorTurno->pago = "pendiente";
                        $jugadorTurno->save();      
                       } 
                       sleep(2);
                       Participa::where('id_juego', $this->element->id_juego)
                       ->where('email',$postulacionGanador)
                       ->update(['turno' => $this->element->nro_turno]);

                         $participas = Participa::where('id_juego',$this->element->id_juego)
                         ->select('email')
                         ->get();
             
                        $listaToken = User::whereIn('email', $participas)->get(); 
                      
                       foreach ($listaToken as $value) {
                          if($value->email == $postulacionGanador){
                          $this->sendNotification("Felicidades !! $value->email ","eres el ganador del turno # {$this->element->nro_turno} del juego {$nodoJuego->nombre} con el monto Bs. $postulacionGanadorMonto",$value->token);
                          }else{
                          $this->sendNotification("Hola!! $value->email ","el ganador del turno # {$this->element->nro_turno} del juego {$nodoJuego->nombre} , fue el jugador $postulacionGanador, con el monto Bs. $postulacionGanadorMonto \n la cuota a pagar en el turno es de $cuotaTurno Bs.",$value->token);
                          }
                        }
                       
                      sleep(2);
            }// end 2do if
            echo "salio del 2do if \n";   
            sleep(2);       
        }else{ // end 1er if
            echo "se rompio el ciclo \n";           
        }    
          echo "se termino el proceso \n";
    }
     

            /*   $participas = Participa::where('id_juego',$this->element->id_juego)
              ->where('turno',null)
              ->select('email')
              ->get();
             
            $listaToken = User::whereIn('email', $participas)->get(); 
           
            foreach ($listaToken as $value) {
               $this->sendNotification("Hola!! $value->email ","el turno # {$this->element->nro_turno} acaba de empezar, tienes 5 min para postular y ser el ganador del turno, ingresa a la app !!",$value->token);
            }
            
     sleep(180);
     $postulacionMaxima = Postulacion::where('id_juego', $this->element->id_juego)
    ->where('turno',$this->element->nro_turno)
    ->select('id_juego','postulante', 'monto','turno')
    ->orderByDesc('monto')
    ->first();
    
    //echo $postulacionMaxima;
    Turno::where('id', $this->element->id)->update(['ganador' => $postulacionMaxima->postulante,'monto'=>$postulacionMaxima->monto]);    
    

    $registros = Participa::where('estado_participa', 'aceptado')
        ->where('email', '!=', $postulacionMaxima->postulante)
        ->get();
      
    echo $registros; 
    echo "____________________________________________________________________________________________"; 
       foreach($registros as $data){
        $jugadorTurno = new JuegoTurno();
        $jugadorTurno->id_turno = $this->element->id;
        $jugadorTurno->jugador = $data->email;
        $jugadorTurno->pago = "pendiente";
        $jugadorTurno->save();      
       } 
        
       Participa::where('id_juego', $this->element->id_juego)
       ->where('email',$postulacionMaxima->postulante)
       ->update(['turno' => $this->element->nro_turno]);    
    


       $listaToken = User::all(); 
           
       foreach ($listaToken as $value) {
           if($value->email == $postulacionMaxima->postulante){
            $this->sendNotification("Felicidades !! $value->email ","eres el ganador del turno # {$this->element->nro_turno} con el monto Bs. $postulacionMaxima->monto ",$value->token);
           }else{
          $this->sendNotification("Hola!! $value->email ","el ganador del turno # {$this->element->nro_turno} fue el jugador $postulacionMaxima->postulante, con el monto Bs. $postulacionMaxima->monto ",$value->token);
           }
       }
   }*/

   public function sendNotification($title,$content,$token)
    {
   $url = 'https://fcm.googleapis.com/fcm/send';

   $FcmToken = $token;
   $serverKey = "AAAA4_mwdcc:APA91bHoqMnxxqFGT3GH4XQ-9Cj8pkh9Pkr7Z8QbeHGuIJ4l8owRwtf0iDgZR10UkgEPIiCy-xAEKfp6bi0gD3mbdGFiPuiNz8kiS8a-Ghob1TAWBUMl0KhOCw1v2oczNgB8AQlxceEN"; // Agrega la clave del servidor proporcionada por FCM aquí

   $data = [
       "to" => $FcmToken, // Utiliza 'to' en lugar de 'registration_ids' para enviar a un solo dispositivo
       "notification" => [
           "title" => $title,
           "body" => $content,  
       ]
   ];
   $encodedData = json_encode($data);

   $headers = [
       'Authorization:key=' . $serverKey,
       'Content-Type: application/json',
   ];

   $ch = curl_init();

   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
   curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
   // Deshabilitar temporalmente el soporte de certificado SSL
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
   // Ejecutar la solicitud POST
   $result = curl_exec($ch);
   if ($result === FALSE) {
       die('Curl failed: ' . curl_error($ch));
   }        
   // Cerrar la conexión
   curl_close($ch);
   // Respuesta de FCM
  }  



}
