<?php namespace Tecnotrade\Manageerrors\Classes;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

class ErrorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Intercetta gli errori di sistema
        Event::listen('exception.beforeRender', function ($exception, $httpCode) {
            
            Log::error($exception); // Salva l'errore nei log

            // Gestione errore 404
            if ($exception instanceof NotFoundHttpException) {
                return Response::view('mycompany.errorhandler::404', [], 404);
            }

            // Gestione errore generico
            return Response::view('mycompany.errorhandler::500', ['message' => $exception->getMessage()], 500);
        });
    }
}