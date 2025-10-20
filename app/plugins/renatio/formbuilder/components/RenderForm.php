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

class RenderForm extends ComponentBase
{
    use SupportLocationFields;

    public $form;

    public $markup;

    public $message;

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

    public function init()
    {
        try {
            $this->form = $this->getForm();

            event('formBuilder.overrideForm', [&$this->form]);

            $this->handleFileUploads();

            $this->addComponent(SpamProtection::class, 'spamProtection');
        } catch (Throwable $throwable) {
            $this->page['formCode'] = $this->getFormCode();
        }
    }

    protected function getForm()
    {
        return Form::query()
            ->with([
                'fields' => fn ($query) => $query->isVisible()->with('field_type'),
            ])
            ->where('code', $this->getFormCode())
            ->firstOrFail();
    }

    protected function getFormCode()
    {
        return $this->getController()->vars['formCode'] ?? $this->property('formCode');
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

    public function onRun()
    {
        $this->markup = $this->getFormMarkup();

        $this->addJs('assets/js/form.min.js?v=5');
    }

    protected function getFormMarkup()
    {
        return $this->form?->fields->reduce(function ($template, $field) {
            $pattern = "/{{\sform_field\('$field->name'\)\s}}/i";

            event('formBuilder.overrideField', [&$field, $this->form]);

            return preg_replace($pattern, $field->html, $template);
        }, $this->form->getMarkup());
    }

    public function onSubmit()
    {
        $validator = (new FormValidator($this->form))->make();

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            event('formBuilder.formSubmitted', [&$this->form]);
        } catch (Throwable $throwable) {
            $this->message = app()->environment('production') ? $this->form->error_message : $throwable->getMessage();

            trace_log($throwable);

            throw new AjaxException([".form-alert-{$this->form->id}" => $this->renderPartial('@message')]);
        }

        return $this->response();
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

    public function getFormCodeOptions()
    {
        return Form::lists('name', 'code');
    }
}
