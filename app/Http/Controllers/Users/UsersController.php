<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Validator;
use App\User;
use JWTAuth;
use Auth;
use Tymon\JWTAuthExceptions\JWTException;

use Illuminate\Support\Collection as Collection;
use DB;
use Hash;

class UsersController extends ApiController
{

    public function __construct(){
      // el usuario de recuperar su token si no lo tiene ya
      $this->middleware('jwt.auth');

   }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['user_sesion'] = Auth::user(); //usuario que inicio sesion

        // Recuperar todos los usuarios de la base de datos y devolverlos
        $data['users'] = DB::table('users')
                        ->select('id', 'role', 'name', 'surname', 'email', 'created_at')
                        ->orderBy('id', 'desc')
                        ->get();

        return $this->showAll(Collection::make($data));
    }


    /*Usando inyección de Modelos,
     *con esto ya no es necesario estar buscando por id, si no que simplemente pasandole el modelo como parametro
     a la función este ya nos hace la busqueda correspondiente como si lo hicieramos con el findOrFail asi,  User::findOrFail($id);
     */
    public function show(User $user){
      //$user = User::find($id);

      //si en caso de que lo que se busca no exista para esp se usa el metodo findOrFail
      //$user = User::findOrFail($id);

      return $this->showOne($user); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user){
      //$user = User::findOrFail($id);

      $rules = [
        'email' => 'email|unique:users,email,' . $user->id,//validamos que el email pueda ser el mismo del usuario actual, es decir el email debe de ser unico pero puede quedar con el mismo valor si es q no es modificado
      ];

      $this->validate($request, $rules);

      //mediante el metodo has verificamos que tengamos un campo con el nombre asignado, en este caso es
      //el campo name, y si este viene entonces lo actualizamos
      if($request->has('name')){
        $user->name = $request->name;
      }

      if($request->has('surname')){
        $user->surname = $request->surname;
      }

      //comprobamos si el email es diferente al q el usuario tiene actualmente
      if($request->has('email') && $user->email != $request->email){
          $user->email = $request->email;
      }

      //comprobamos si el usuario a cambiado su contrasea
      if($request->has('new_password')){
         //en caso de que si comparamos que la contrasea enviada sea la misma a la de la bd
        if (Hash::check($request->password, $user->password)) {

                //validamos las nuevas contrasea
                $rule_pass = [
                  'new_password' => 'min:6',//la coontrasea debe de ser confirmada con un campo llamado password_confirmation
                ];

               $this->validate($request, $rule_pass);

               if($request->new_password != $request->password_confirmation){
                    return $this->errorResponse('El campo de confirmación de la nueva contraseña no coincide. ', 422);
               }
                
               $user->password = bcrypt($request->new_password);

            } else {
                //si no coincide retornamos un msj de error
                return $this->errorResponse('Tu contraseña actual no coincide. ', 422);
                
            }
          
      }

       //el metodo isDirty valida si algunos e los valores originales ah cambiado su valor
       if(!$user->isDirty()){
         return $this->errorResponse('Se debe de especificar un valor diferente para actualizar', 422);
       }

       $user->save();

       return $this->showOne($user, "Usuario $user->name actualizado exitosamente!", 201);



    }


}
