<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\Juego;
use App\Models\Notification;
use App\Models\Participa;
use App\Mail\DemoEMail;
use App\Http\Requests\JuegoControllerFormRequest;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;


class JuegoController extends Controller
{
  
    /**
     * Display a listing of the resource.
     */
    public function index($metodo,$email)
    {
       /* $firebase = (new Factory)
            ->withServiceAccount('/public/pasanaku-cc88b-firebase-adminsdk-icarn-0670002b2b.json')
            ->create();

         $messaging = $firebase->getMessaging();
         $message = CloudMessage::withTarget('token',"dzSmkBHDTLWWC6tUEBVlfy:APA91bErLJm0Xlo_c_5uxaV7EBwqSQA60465gGxLLj7fWXgaahtE5jYKDhMjOGWBGcaSyeeKCfHh5BKwHG2ojCwWqIRbLFvHq_7V7kdQcAu-bJPvEr5k-P73hRYD_s4VuTIi2Hh8lm-f")
         ->withNotification([
        'title' => 'Título de la notificación',
        'body' => 'Cuerpo del mensaje de la notificación',
    ]);

$messaging->send($message);*/
  
        switch ($metodo) {
            case 'getJuego':
                $juegos = Juego::where('usuario', $email)->get(); 
                return response()->json($juegos); 
            case 'getFallido':
                return $this->getCorreoFallido();
            // Agrega más casos según sea necesario
            default:
                return response()->json(['error' => 'Método no válido'], 404);
        }
    }

    public function getPendiente($id){
       $participas = Participa::where('id_juego',$id)->get(); 
       return response()->json($participas); 
    }
    
    public function getCorreoFallido(){
        $participas = Participa::where('invitacion', 'fallido')->get();
        return response()->json($participas);
    }
     

    /**
     * Store a newly created resource in storage.
     */
     
   // public $listaCorreosFallidos = [];
    public function enviarCorreos($invitados){
        $listaCorreos = [];
        foreach ($invitados as $data) {
                $listaCorreos[] = $data["email"];
        }

        try{
            Mail::to($listaCorreos)->send(new DemoEmail());
        }catch(\Exception $e){
          echo $e; 
        }
    } 

    public function store(Request $request)
    {
      
   // Mail::to("gaticavictor4@gmail.com")->send(new DemoEmail());
        $rules = ['nombre'=>'required|string|min:1|max:100'];
        $validator = \Validator::make($request->input(),$rules);
        if ($validator->fails()){
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()->all()
            ],400);
        }
        $juego = new Juego($request->input());
        $juego->save(); 

        //Obtener el ID del juego recién creado
        $juego_id = $juego->id;
               foreach ($request["listaObjetos"] as $invitado) {
                $nuevoObjeto = new Participa();
                $nuevoObjeto->id_juego = $juego_id;
                $nuevoObjeto->telefono = $invitado["telefono"];
                $nuevoObjeto->email = $invitado["email"];
                $nuevoObjeto->estado_participa ="pendiente";
                $nuevoObjeto->save();
               }

              $this->enviarCorreos($request["listaObjetos"]);
              
              $participas = Participa::where('id_juego',$juego->id)
              ->select('email')
              ->get();
             
            $listaToken = User::whereIn('email', $participas)->get(); 
           
            foreach ($listaToken as $value) {
               $this->sendNotification("Hola!! $value->email ","tienes una invitacion del juego pasanaku: $juego->nombre , si deseas aceptarla, ingresa a la app pasanaku ",$value->token);
            }
           
          //  $this->sendNotification("se creo el juego pasanaku ${$juego->nombre}","si deseas participar, acepta la invitacion desde tu app movil PASANAKU",$tokens);
         return response()->json(['id_juego'=>$juego->id,"participantes"=>$participas,'tokens'=>$listaToken]);
       /* return response()->json([
            'status'=>true,
            'message'=>'juego creado satisfactoriamente',
        ],400);*/
    }

    /**
     * Display the specified resource.
     */
    

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
