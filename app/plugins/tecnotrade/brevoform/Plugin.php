<?php namespace Tecnotrade\Brevoform;

use System\Classes\PluginBase;
use Tecnotrade\Brevoform\Classes\ApiBrevoFormSender;
use Event;
use Tecnotrade\Testbrevo\Components\ApiBrevoFormBuilder as ComponentsApiBrevoFormBuilder;
use Tailor\Models\GlobalRecord;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;
use Exception;
use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\Api\AttributesApi;
use SendinBlue\Client\Model\CreateContact;

/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        $this->extendOverrideRenatioSubmitForm();
    }

    public function extendOverrideRenatioSubmitForm(){
        
        Event::listen('formBuilder.beforeSendMessage', function ($form, $data) {
     
            // Intercetta i dati del form e inviali a Brevo
            $formData=post();
            \Log::info('SEND FORM TO BREVO FROM NEW');
            $brevoSender=new ApiBrevoFormSender;
            $brevoSender->submitRenatioForBrevo($formData);
            return true;
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
