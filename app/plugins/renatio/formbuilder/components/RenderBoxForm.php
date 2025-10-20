<?php

namespace Renatio\FormBuilder\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Exception\AjaxException;
use October\Rain\Exception\ValidationException;
use Renatio\FormBuilder\Classes\FormValidator;
use Renatio\FormBuilder\Models\Form;
use Renatio\FormBuilder\Models\FormLog;
use Renatio\FormBuilder\Traits\SupportLocationFields;
use Renatio\SpamProtection\Components\SpamProtection;
use System\Models\File;
use Throwable;

class RenderBoxForm extends ComponentBase
{
    use SupportLocationFields;

    public $form;

    public $markup;

    public $message;
    public $formCode;

    public $messageType = 'danger';

    public $hasFiles = false;

    public function componentDetails()
    {
        return [
            'name' => 'renatio.formbuilder::lang.render_form.name',
            'description' => 'renatio.formbuilder::lang.render_form.description',
            'snippetAjax' => true,
        ];
    }

    public function defineProperties()
    {
        return [
            'formCode' => [
                'title' => 'renatio.formbuilder::lang.form.title',
                'description' => 'renatio.formbuilder::lang.form.description',
                'type' => 'dropdown',
                'validation' => ['required' => true],
                'default' => 'default-form',
            ],
        ];
    }
    public function boxesInit()
    {
        // Recupera la pagina e il box dai metodi getBoxesPage e getBoxesBox
        $page = $this->getBoxesPage();
        $box = $this->getBoxesBox();
        //dd($box->renatio_form_id);
        // Esegui la logica di inizializzazione personalizzata
        if ($box && $box->renatio_form_id) {
            $this->formCode = $box->renatio_form_id;
            $this->property('formCode', $box->renatio_form_id);
            //parent::init();  // Aggiungi questa riga per chiamare il metodo init del componente base.

        // Chiamare boxesInit() per aggiornare formCode
        

        try {
            $this->form = $this->getForm();

            $this->handleFileUploads();

            $this->addComponent(SpamProtection::class, 'spamProtection');
        } catch (Throwable $throwable) {
            $this->page['formCode'] = $this->property('formCode');
        }
        }
    }
    /*public function init()
    {
        dd("prima");
        parent::init();  // Aggiungi questa riga per chiamare il metodo init del componente base.

        // Chiamare boxesInit() per aggiornare formCode
        

        try {
            $this->form = $this->getForm();

            $this->handleFileUploads();

            $this->addComponent(SpamProtection::class, 'spamProtection');
        } catch (Throwable $throwable) {
            $this->page['formCode'] = $this->property('formCode');
        }
    }*/

    public function onRun()
    {
        $this->markup = $this->getFormMarkup();

        $this->addJs('assets/js/form.js?v=4');
    }

    public function onSubmit()
    {
        $validator = (new FormValidator($this->form))->make();

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            event('formBuilder.formSubmitted', [$this->form]);
        } catch (Throwable $throwable) {
            $this->message = app()->environment('production') ? $this->form->error_message : $throwable->getMessage();

            trace_log($throwable);

            throw new AjaxException([".form-alert-{$this->form->id}" => $this->renderPartial('@message')]);
        }

        return $this->response();
    }

    public function getFormCodeOptions()
    {
        return Form::lists('name', 'code');
    }

    protected function getForm()
    {
        
        return Form::query()
            ->with([
                'fields' => fn($query) => $query->isVisible()->with('field_type'),
            ])
            ->where('code', $this->formCode)
            ->firstOrFail();
    }

    protected function response()
    {
        if ($this->form->redirect_to) {
            return redirect()->to($this->form->redirect_to);
        }

        $this->messageType = 'success';
        $this->message = $this->form->success_message;

        return [".form-alert-{$this->form->id}" => $this->renderPartial('@message')];
    }

    protected function getFormMarkup()
    {
        return $this->form?->fields->reduce(function ($template, $field) {
            $pattern = "/{{\sform_field\('$field->name'\)\s}}/i";

            return preg_replace($pattern, $field->html, $template);
        }, $this->form->getMarkup());
    }

    protected function handleFileUploads()
    {
        $this->hasFiles = ! $this->form->uploadFields()->isEmpty();

        foreach ($this->form->uploadFields() as $field) {
            FormLog::extend(function ($model) use ($field) {
                $attachType = $field->has_multiple_files ? 'attachMany' : 'attachOne';

                $model->$attachType[$field->name] = [
                    File::class,
                ];
            });
        }
    }
}
