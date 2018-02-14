<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'users';


  //atributos que pueden ser asignados de manera masiva, una asignasion masiva de manera masiva en laravel cuando se realiza
  //el establecimiento de tal atributo por medio del metodo create o update
    protected $fillable = [
        'role',
        'name', 
        'surname',
        'email', 
        'password',
    ];


    /*
     * - Mutadores y accesores en los modeo
     * - Son metodos que se implementan en los modelos para la modificación de un atributo y  para acceder a dicho valor
     *
     * -- Un mutador se utiliza para modificar el valor original de un atributo antes de hacer la insercion en la base de datos
     *
     *-- El accesor se utiliza para modificar el valor de un atributo despuesn de haberlo obtenido de la base de datos
    */

    /*Antes de insertar el nombre requiere que todos los caracteres esten en minuscula excepto el inicial, entonces en este
      caso vamos a usur un mutador y un accesor*/
      //mutador
      public function setNameAttribute($valor){
        $this->attributes['name'] = strtolower($valor);//para que el nombre siempre se inserte en minuscula
      }

      public function setSurnameAttribute($valor){
        $this->attributes['surname'] = strtolower($valor);
      }

      //accesor
      public function getNameAttribute($valor){
        //con esto estamos retornando el valor del nombre siempre con la primera letra de cada palabra en mayuscula sin la necesidad de modificarlo en la bd
        return ucwords($valor);
      }

      public function getSurnameAttribute($valor){
        return ucwords($valor);
      }



      /*Para el correo electronico solo vamos a poner todo en minuscula por lo cual solo usaremos un mutador */
      public function setEmailAttribute($valor){
        $this->attributes['email'] = strtolower($valor);//para que el email siempre se inserte en minuscula
      }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    //atributos ocultos
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function tasks() {
        return $this->hasMany(Task::class);
    }

}
