<?php

namespace App\Http\Controllers;

use App\Models\Participa;
use App\Models\JuegoTurno;
use App\Models\User;
use App\Models\Juego;
use App\Models\Turno;
use App\Models\Postulacion;
use App\Http\Requests\ParticipaControllerFormRequest;
use App\Http\Requests\JuegoControllerFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParticipaController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function getDataJugador(Request $request){
          $participas = Juego::join('participas', 'juegos.id', '=', 'participas.id_juego')
          ->where("participas.id_juego",$request->input("idJuego"))
          ->where('participas.email',$request->input('email'))->get();

          return response()->json($participas);
    } 
    public function getDataJuego(Request $request){
        $participas = Juego::join('participas', 'juegos.id', '=', 'participas.id_juego')
        ->where('participas.id_juego', $request->input('id'))
        ->get();
        return response()->json($participas); 
     }


    public function registroPostulacionJugador(Request $request){
        $postulacion = new Postulacion();
        $postulacion->id_juego = (int)$request->input('id_juego');
        $postulacion->turno = (int)$request->input('turno');
        $postulacion->postulante = $request->input('email');
        $postulacion->monto = (int)$request->input('monto');
    
        // Puedes asignar valores a más columnas según sea necesario
        $postulacion->save();
        return response()->json($postulacion);
    }
    
    public function getDataTurnoJugador(Request $request){
        $fechaActual= Carbon::now('America/La_Paz')->toDateString();

     $consulta = Juego::select('juegos.id','juegos.monto','juegos.nombre', 'turnos.nro_turno', 'turnos.fecha_inicio', 'turnos.fecha_fin')
    ->join('participas', 'juegos.id', '=', 'participas.id_juego')
    ->join('turnos', 'juegos.id', '=', 'turnos.id_juego')
    ->where('juegos.estado_juego', '=', 'iniciado')
    ->where('participas.estado_participa', '=', 'aceptado')
    ->where('participas.turno','=',null)
    ->where('participas.email', '=', $request->input('email'))
    ->whereDate('turnos.fecha_inicio', '<=', $fechaActual)
    ->whereDate('turnos.fecha_fin', '>=', $fechaActual)
    ->get();
        return response()->json($consulta);
  } 

  public function getDataTurno(Request $request){
    $turno = Turno::where('id_juego',$request->input('id_juego'))
                ->orderBy('nro_turno')
                ->get();
       return response()->json($turno);
  }

  public function getDataJugadoresTurno(Request $request){
       $jugadores = JuegoTurno::where('id_turno',$request->input('id_turno'))->get();
       return response()->json($jugadores);
  }

  public function verificarPostulacionJugador(Request $request){
    $resultados = DB::table('postulacions')
    ->where('id_juego',(int)$request->input('idJuego'))
    ->where('turno',(int)$request->input('idTurno'))
    ->where('postulante',$request->input('email'))
    ->get();
    return response()->json($resultados);
  }
  
  

    public function index(Request $request)
    {
        $participas = Juego::join('participas', 'juegos.id', '=', 'participas.id_juego')
        ->where('participas.email', $request->input('email'))
        ->select('juegos.*', 'participas.*') // Selecciona los campos específicos que deseas obtener
        ->get();
        return response()->json($participas);
    }
     
    public function getDataQRJugador($email)
    {
        $user = User::where('email',$email) // Selecciona los campos específicos que deseas obtener
        ->get();
        return response()->json($user);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        //return response()->json($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Participa $participa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        Participa::where('id_juego', $id)
        ->where('email',$request->input('email'))
        ->update(['estado_participa' => $request->input('estado_participa')]);
        return response()->json(["status"=>"true","estado"=>$request->input('estado_participa')]);
    }

    public function updateImageQrJugador(Request $request,$email){
         User::where('email',$email)
       ->update(['imageQR'=>$request->input('imageQR')]);
         return response()->json(["image"=>$request->input('imageQR')]);   
    }
    
    public function updatePagoJugador(Request $request,$id){
        JuegoTurno::where('id_turno',$id)
        ->where('jugador',$request->input('email'))
      ->update(['pago'=>'realizado']);
        return response()->json(["image"=>$request->input('email')]);   
   }

    
    public function updateFechaJuego(Request $request,$id)
    {
        $resultado = Juego::where('id', $id)
        ->update(['fecha_inicio' => $request->input('fecha')]);

    if ($resultado > 0) {
        return response()->json(["status" => "true", "id" => $id, "fecha" => $request->input("fecha")]);
    } else {
        return response()->json(["status" => "false", "message" => "No se pudo actualizar el juego"]);
    }
    
    } 

       
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Participa $participa)
    {
        //
    }
}
