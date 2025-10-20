<?php namespace Tecnotrade\Extendboxesform;

use System\Classes\PluginBase;
use Offline\Boxes\Models\Box;
use Renatio\FormBuilder\Models\Form;
use Event;

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
        // Estende il modello Box per includere il metodo getRenatioForms
        Box::extend(function ($model) {
            $model->addDynamicMethod('getRenatioForms', function() {
                return Form::all()->pluck('name', 'id')->toArray();
            });
        });
        Box::extend(function ($model) {
           
            $model->addDynamicMethod('getRenatioFormsCode', function() {
                return Form::all()->pluck('name', 'code')->toArray();
            });
        });
        /*
        Event::listen('formBuilder.overrideForm', function (&$form) {
            $controller = \Cms\Classes\Controller::getController();
        
            // Verifica la lista dei box associati alla pagina attuale
            $a='vuoto';
            foreach ($controller->vars['boxesPage']->boxes as $box) {
                // Verifica se il box usa un partial specifico del tuo plugin
                 
                if (isset($box->data['renatio_form_id'])) {
                    
                    $formCode = $this->getFormCodeById($box->data['renatio_form_id']);
                  
                    // Verifica il contesto specifico di extendboxesform
                    if ($formCode) {
                        $form->code = $formCode;
                        break;  // Una volta trovato il formCode, esci dal ciclo
                    }
                }
            }
            
        });
        */

        // Ascolta l'evento formBuilder.overrideForm per sovrascrivere il formCode
        
    }

    // Funzione per ottenere il formCode basato sull'ID del form
    protected function getFormCodeById($formId)
    {
        $form = Form::find($formId);
        return $form ? $form->code : null;
    }
    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            'Tecnotrade\Extendboxesform\Components\CustomRenderForm' => 'customRenderForm',
        ];
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'renatioFormBuilderGetForm' => function($formId) {
                    $f = Form::find($formId);
                    if($f){
                        return $f->code;
                    }
                    return 0;
                    
                },
            ],
        ];
    }
}
