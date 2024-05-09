<?php

namespace App\Http\Controllers;
use App\Models\Juego;
use App\Models\User;
use App\Http\Requests\AuthControllerFormRequest;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && password_verify($request->password, $user->password)){
            return response()->json([
                'status' => 'true',
                'email' => $request->email,
            ]);
        }else{
            return response()->json([
                'status' => 'false'
            ]);
        }
    }
    
    public function obtenerUsuario(Request $request){
        $user = User::where('email',$request->email)->get(); 
        return response()->json($user);
    }

    public function store(Request $request)
    {

      // Crear un nuevo usuario con los datos recibidos
    /*$user = new User([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => $request->input('password'),
        'token' => $request->input('token')
    ]);*/

    $user = new User(); 
    $user->name=$request->input('name');
    $user->email=$request->input('email');
    $user->password=$request->input('password');
    $user->token=$request->input('token');

    // Guardar el usuario en la base de datos
    $user->save();

    // Devolver una respuesta indicando que el usuario ha sido registrado

    return response()->json([
        'message' => 'Usuario registrado correctamente',
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password'=> $request->input('password'),
        'token'=> $request->input('token'),
      //  'user' => $user
    ], 200);
}

 


}


