<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JuegoController;
use App\Http\Controllers\ParticipaController;
use App\Http\Controllers\AuthController; 


/*

|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::resource('auth',AuthController::class);

Route::resource('participa',ParticipaController::class);
Route::get('participaJugador', 'ParticipaController@getDataJugador');
// Alternativa para declarar la ruta utilizando el método `get`

Route::get('participaJugador', [ParticipaController::class, 'getDataJugador']);
Route::get('juegoData', [ParticipaController::class, 'getDataJuego']);

Route::get('turnoJugador', [ParticipaController::class, 'getDataTurnoJugador']);
Route::get('verificarPostulacion', [ParticipaController::class, 'verificarPostulacionJugador']);
Route::post('postulacionJugador', [ParticipaController::class, 'registroPostulacionJugador']);


Route::put('participa/{id}', 'ParticipaController@update');

Route::put('editQRJugador/{email}', [ParticipaController::class, 'updateImageQRJugador']);

Route::put('editFechaJuego/{id}', [ParticipaController::class, 'updateFechaJuego']);

Route::put('actualizaPago/{id}', [ParticipaController::class, 'updatePagoJugador']);

Route::get('juegoPendiente/{id}', [JuegoController::class, 'getPendiente']);

Route::get('send-push-notification', 'PushNotificationController@sendPushNotification');


Route::get('obtenerQRJugador/{email}', [ParticipaController::class, 'getDataQRJugador']);

Route::get('obtenerGanadorTurno', [ParticipaController::class, 'getDataTurno']);
Route::get('obtenerJugadoresTurno', [ParticipaController::class, 'getDataJugadoresTurno']);

Route::get('getUsuario', [AuthController::class, 'obtenerUsuario']);


Route::resource('juegos/{metodo}/{email}',JuegoController::class);





