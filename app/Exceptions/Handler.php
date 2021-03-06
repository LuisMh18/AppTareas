<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use App\Traits\ApiResponser;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Illuminate\Database\QueryException;

class Handler extends ExceptionHandler
{

  use ApiResponser;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        //errores de validacion
        if($exception instanceof ValidationException){
          return $this->convertValidationExceptionToResponse($exception, $request);
        }

        //errores cuando se busca un registro que no existe
        if($exception instanceof ModelNotFoundException){
          $modelo = strtolower(class_basename($exception->getModel()));
          return $this->errorResponse("No existe ninguna instancia de {$modelo} con el id especificado", 404);
        }

        //errores para la AuthenticationException
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        //errores de autorización
        if ($exception instanceof AuthorizationException) {
            return $this->errorResponse('No posee permisos para ejecutar esta acción', 403);
        }

        //errores cuando no se encuenra la url especificada
        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('No se encontro la URL especificada', 404);
        }

        //errores de metodos no permitidos
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('El método especificado en la petición no es valido', 405);
        }

        //errores para las diferentes excepciones de http
        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        //manejando la eliminación de recursos relacionados
        if ($exception instanceof QueryException) {
            $codigo = $exception->errorInfo[1];
            if($codigo == 1451){
              return $this->errorResponse('No se puede eliminar de forma permanente el recurso porque está relacionado con algún otro.', 409);
            }

        }

        if ($exception instanceof TokenMismatchException) {
            return redirect()->back()->withInput($request->input());
        }

        //manejando excepciones inesperadas
        if(config('app.debug')){
          return parent::render($request, $exception);
        }
        return $this->errorResponse('Falla inesperada. Intente luego', 500);


    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->isFrontend($request)) {
            return redirect()->guest('login');
        }
        return $this->errorResponse('No autenticado.', 401);        
    }

    //funcion para retornar los errores de validacion de laravel en formato json
    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

        if ($this->isFrontend($request)) {
            return $request->ajax() ? response()->json($errors, 422) : redirect()
                ->back()
                ->withInput($request->input())
                ->withErrors($errors);
        }

        return $this->errorResponse($errors, 422);
    }

    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }



}
