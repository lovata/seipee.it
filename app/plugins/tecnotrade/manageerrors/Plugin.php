<?php namespace Tecnotrade\Manageerrors;

use System\Classes\PluginBase;
use Illuminate\Contracts\Http\Kernel;
use Tecnotrade\Manageerrors\Middleware\HandleErrors;
use App;
use Log;
use Exception;
use Redirect;
use Request;
use Response;
use October\Rain\Exception\AjaxException;
use Backend\Facades\BackendAuth;
use Session;
use Mail;
use Config;
use Tailor\Models\GlobalRecord;;

/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Error Handler',
            'description' => 'Intercetta e mostra tutti gli errori nel sistema',
            'author'      => 'Tecnotrade',
            'icon'        => 'icon-bug'
        ];
    }
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot() {
        return;

        App::error(function (Exception $exception) {
            // Se l'errore proviene dal backend, lo lasciamo gestire a October CMS
            if (BackendAuth::check()) {
                return;
            }

            $global = GlobalRecord::findForGlobalUuid('tecnotrade');
            $adminEmail = $global?->app_debug_mail ?? 'default@mail.com';

            Log::info('EMAIL PRESA DAL GLOBAL');
            Log::info($adminEmail);

            $session = Session::all();
            Log::info('VARIABILI DI SESSIONE');
            Log::info($session);

            $cookies = print_r($_COOKIE, true);

            // Ottieni lo slug della pagina
            $currentUrl = Request::url(); // URL completo della pagina
            $slug = Request::path();
            $siteName = Config::get('app.name', 'Nome del sito predefinito');

            // Scrive l'errore nei log di sistema
            Log::error($exception);
            Log::info('Sono entrato in APP ERROR');
            Log::info('URL della pagina');
            Log::info($currentUrl);
            Log::info('Nome del sito');
            Log::info($siteName);
            // Salva il codice di errore e il messaggio nella sessione
            $errorCode = $exception->getCode();
            $errorMessage = $exception->getMessage();
            $fileError = $exception->getFile();
            $lineError = $exception->getLine();


            \Tecnotrade\ManageErrors\Models\Error::create([
                'page_url' => $currentUrl,
                'error_code' => $errorCode,
                'error_file' => $fileError,
                'error_line' => $lineError,
                'error_message' => $errorMessage,
                'error_date' => now(), // Imposta la data e ora attuale come timestamp
            ]);

            try {
                Mail::send('tecnotrade.manageerrors::mail.error_notification', [
                    'site_name' => $siteName,
                    'page_url' => $currentUrl,
                    'error_code' => $errorCode,
                    'error_file' => $fileError,
                    'error_line' => $lineError,
                    'error_message' => $errorMessage,
                    'error_date' => now(),
                    'session_data'  => print_r(Session::all(), true),
                    'cookies'       => $cookies,

                ], function ($message) use ($adminEmail) {
                    $message->to($adminEmail);
                    $message->subject('Notifica di Errore');
                });
            } catch (\Exception $error) {

            }

            // Se la richiesta è AJAX, usa AjaxException per restituire un errore "smart"
            if (Request::ajax()) {
                throw new AjaxException([
                    '#flashMessages' => '<p class="error">Si è verificato un errore. Riprova più tardi.</p>'
                ]);
            }

            // Se non è AJAX, reindirizza alla pagina di errore personalizzata
            return Redirect::to('/error');
        });
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
