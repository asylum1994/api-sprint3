<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    public function sendNotification($title, $content, $deviceTokens)
{
    $url = 'https://fcm.googleapis.com/fcm/send';

    // Agrega la clave del servidor proporcionada por FCM aquí
    $serverKey = "AAAA4_mwdcc:APA91bHoqMnxxqFGT3GH4XQ-9Cj8pkh9Pkr7Z8QbeHGuIJ4l8owRwtf0iDgZR10UkgEPIiCy-xAEKfp6bi0gD3mbdGFiPuiNz8kiS8a-Ghob1TAWBUMl0KhOCw1v2oczNgB8AQlxceEN";

    // Prepara los datos de la notificación
    $data = [
        "notification" => [
            "title" => $title,
            "body" => $content,
        ]
    ];

    $headers = [
        'Authorization:key=' . $serverKey,
        'Content-Type: application/json',
    ];

    // Itera sobre los tokens de los dispositivos y envía la notificación a cada uno
    foreach ($deviceTokens as $FcmToken) {
        
        $data["to"] = $FcmToken;
        $encodedData = json_encode($data);

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
        dd($result);
    }
}

      
}
