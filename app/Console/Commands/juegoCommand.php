<?php
namespace App\Console\Commands;

use App\Models\Juego;
use App\Models\Participa;
use App\Models\User;
use App\Jobs\TurnoActual;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Console\Commands\turnoCommand;


class juegoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:juego-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizamos el estado del juego';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = now()->toDateString();

        // Obtener los juegos que están pendientes y cuya hora de inicio coincide con la hora actual
        $juegos = Juego::where('estado_juego', 'pendiente')
                      ->whereDate('fecha_inicio', $fechaActual)
                      ->get();
                      
        // Actualizar el estado de los juegos encontrados a "iniciado"
        foreach ($juegos as $juego) {
            $juego->estado_juego = 'iniciado';
            $juego->save();
            sleep(2);

            $participas = Participa::where('id_juego',$juego->id)
              ->where('estado_participa','aceptado')
              ->select('email')
              ->get();
             
            $listaToken = User::whereIn('email', $participas)->get(); 
            
            foreach ($listaToken as $value) {
               $this->sendNotification("Hola!!  $value->email","el juego pasanaku : $juego->nombre acaba de empezar , ingresa a la app para mas detalles",$value->token);
            }
            echo "se envio el juego \n";
           // TurnoActual::dispatch($juego);

        }
        
        $this->info($fechaActual);
    }

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
