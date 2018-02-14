<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use AppHttpRequests;
use AppHttpControllersController;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\User;
use Auth;

class AuthenticateController extends Controller{

  public function __construct(){
      // Aplicar el middleware jwt.auth a todos los métodos de este controlador
      // excepto el método authenticate. No queremos evitar
      // el usuario de recuperar su token si no lo tiene ya
      //$this->middleware('jwt.auth', ['except' => ['login']]);

       //Route::group(['middleware' => 'authenticated'], function () {
   }

  public function index(){

    $data = [];
    $data['user_sesion'] = Auth::user(); //usuario que inicio sesion

    // Recuperar todos los usuarios de la base de datos y devolverlos
    $data['users'] = User::all();
    return response()->json([
                     'error' => false,
                     'data' => $data,
                     'status' => 'success',
                      200
            ]);
   }


   public function show(){
     return response()->json([
                      'error' => false,
                      'data' => "Hola Mundo",
                      'status' => 'success',
                       200
             ]);
   }

   public function login(Request $request){

       //validamos el email y la contraseña
       $v = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);


        if ($v->fails()) {
            return response()->json([
             'error' => 'validate',
             'errors' => $v->errors()
            ]);
        }

       $credentials = $request->only('email', 'password');

          try {
              // verifique las credenciales y cree un token para el usuario
              if (!$token = JWTAuth::attempt($credentials)) {
                  /*-- Nota:
                   * Error 401 - no autorizado: -> indica que se denegó el acceso a causa de las credenciales no válidas.*/
                  $errors = ['El email o la contraseña son incorrectos'];
                  return response()->json([
                           'error' => true,
                           'data' => '',
                           'errors' => $errors,
                            401
                  ]);
              }
          } catch (JWTException $e) {
              // algo salió mal > error interni del servidor (500)
              return response()->json(['error' => 'could_not_create_token'], 500);
          }


       // si no se encuentran errores, podemos devolver un JWT
       return response()->json(compact('token'));
   }



}
