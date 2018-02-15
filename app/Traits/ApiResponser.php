<?php
namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser{
  //metodo para los mensajes success
  public function successResponse($data, $code){
      return response()->json(['data' => $data], $code);
  }

  //metodo para los mensajes de error
  public function errorResponse($message, $code){
      return response()->json(['error' => $message, 'code' => $code], $code);
  }

  //metodo para mostrar una respuesta de elementos
  /*protected function showAll(Collection $collection, $code = 200)
	{

		return $this->successResponse(['data' => $collection], $code);

	}*/

  protected function showAll(Collection $collection, $code = 200)
  {

    $collection = $this->sortData($collection);//ordenación
    $collection = $this->paginate($collection);//paginación

    return $this->successResponse($collection, $code);

  }

  //metodo que mostrara una instancia especifica, por ejemplo cuando tenemos una instancia de un usuario existente
  public function showOne(Model $instance, $message = '', $code = 200){
    return $this->successResponse(
                    [
                      'data' => $instance,
                      'message' => $message
                    ],
                     $code
                   );
  }

  
  public function showMessage($message, $code = 200){
    return $this->successResponse(['message' => $message], $code);
  }


  /* metodo para la ordenación de resultados segun el cliente lo desee ya se por nombre, correo, etc
   * recibe la colección de datos, y la transformación para poder identificar el atributo por el cual se va hacer la ordenación
   * la ordenación se ordenara unicamente si recibimos un prametro en la url llamado sort_by, asi que primero
   * verificamos si viene este atributo, si existe entonces obtenemos su valor para ordenarlo, por suerte como estaos haciendo uso de las
   * colecciones de laravel tenemos aceso a multiples metodos lo cual incluye un metodo para ordenar la colección como tal que es el
   * metodo sortBy el cual recibe el atributo q utilizaremos para ordenar, entonces obtenemos el atributo mandado que tiene que ser el 
   * atributo como esta en la transformación y llamamos al metodo originalAttribute para comparar con el atributo original de la bd
   */
  protected function sortData(Collection $collection)
  {
    if (request()->has('sort_by')) {
      $attribute = request()->sort_by;

      $collection = $collection->sortBy->{$attribute};
    }
    return $collection;
  }
  

  //metodo para la paginación 
  protected function paginate(Collection $collection)
  {

    /*Reglas para permitirle al usuario definir el numero de la paginación, como minimo seran 2 y como maximo 50 */
    $rules = [
      'per_page' => 'integer|min:2|max:50'
    ];

    Validator::validate(request()->all(), $rules);//validamos

    $page = LengthAwarePaginator::resolveCurrentPage();

    $perPage = 10; //valor predifinido a la cantidad de elementos por pagina
    //si rcibimos el parametro per_page sustituimos el valor de la cantidad de elementos de pagina por defecto por el recibido
    if (request()->has('per_page')) {
      $perPage = (int) request()->per_page;
    }

    $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

    $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
      'path' => LengthAwarePaginator::resolveCurrentPath(),
    ]);

    $paginated->appends(request()->all());

    return $paginated;
  }


}


 ?>
