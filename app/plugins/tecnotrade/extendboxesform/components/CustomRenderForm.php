<?php namespace Tecnotrade\Extendboxesform\Components;

use Renatio\FormBuilder\Components\RenderForm;

class CustomRenderForm extends RenderForm
{
    public function onRender()
    {
        //$this->addJs('/plugins/tecnotrade/extendboxesform/assets/js/form.js');
        $this->addJs('/plugins/renatio/formbuilder/assets/js/form.js');
        
        // Recupera il formCode dal box o da dove lo stai passando
        $formCode = $this->property('formCode');

        if ($formCode) {
            // Forza il caricamento del form corretto
            $this->form = null; // Elimina qualsiasi stato precedente
            $form = $this->getFormByCode($formCode);

            if ($form) {
                $this->form = $form;
                $this->setProperty('formCode', $formCode); // Forza il settaggio del formCode
            }
        } else {
            \Log::info('formCode non impostato');
        }

    }

    protected function getFormByCode($formCode)
    {
        return \Renatio\FormBuilder\Models\Form::where('code', $formCode)->first();
    }
}
