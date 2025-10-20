<?php namespace Tecnotrade\Manageerrors\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class HandleErrors
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            // Log dell'errore completo

            Log::error("Errore intercettato nel middleware: " . $e->getMessage());
            Log::error("Errore: " . $e->getMessage(), [
                'url' => $request->fullUrl(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Recupera informazioni dettagliate dell'errore
            $errorData = [
                'message' => $e->getMessage(),
                'code' => $e instanceof HttpException ? $e->getStatusCode() : 500,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];

            // Mostra una pagina dettagliata con informazioni sull'errore
            return Response::view('tecnotrade.manageerrors::debug', $errorData, 500);
        }
    }
}