<?php

namespace Renatio\FormBuilder\Listeners;

use Illuminate\Mail\Message;
use October\Rain\Support\Facades\Mail;
use Renatio\FormBuilder\Models\FormLog;

class SendEmailMessage
{
    protected $form;

    protected $data;

    public function handle($form)
    {
        $this->form = $form;

        $this->data = $this->setData();

        $result = event('formBuilder.beforeSendMessage', [$form, $this->data], true);

        if ($result !== false) {
            $this->sendMessage();

            $this->sendAutoresponderMessage();
        }
    }

    protected function setData()
    {
        $data = $this->form->getDataArray();

        $data['form'] = $this->form;

        if ($extendedData = event('formBuilder.extendFormData', [$data], true)) {
            return $extendedData;
        }

        return $data;
    }

    protected function sendMessage()
    {
        if (! $this->form->template_code) {
            return;
        }

        Mail::send($this->form->template_code, $this->data, function (Message $message) {
            $message->from($this->form->from_email, $this->form->from_name);

            $this->setRecipients($message);

            $this->setReplyTo($message);

            if ($this->form->is_log_enabled) {
                $log = $this->setHeaders($message);

                $this->attachFiles($message, $log);
            }
        });
    }

    protected function setRecipients($message)
    {
        if (! empty($this->form->recipients)) {
            $message->to($this->parseRecipients($this->form->recipients));
        }

        if (! empty($this->form->cc_recipients)) {
            $message->cc($this->parseRecipients($this->form->cc_recipients));
        }

        if (! empty($this->form->bcc_recipients)) {
            $message->bcc($this->parseRecipients($this->form->bcc_recipients));
        }
    }

    protected function parseRecipients($recipients)
    {
        return collect($recipients)
            ->mapWithKeys(fn ($recipient) => [$recipient['email'] => $recipient['recipient_name']])
            ->all();
    }

    protected function setReplyTo($message)
    {
        $email = $this->data[$this->form->reply_email] ?? null;
        $name = $this->data[$this->form->reply_name] ?? null;

        if ($email) {
            $message->replyTo($email, $name);
        }
    }

    protected function setHeaders($message)
    {
        $log = FormLog::saveForm($this->form);

        $message->getHeaders()
            ->addTextHeader('X-RENATIO-LOG', $log->id);

        return $log;
    }

    protected function attachFiles($message, $log)
    {
        return $this->form->uploadFields()
            ->filter(fn ($field) => $field->send_file_as_mail_attachment)
            ->filter(fn ($field) => $log->{$field->name})
            ->each(function ($field) use ($message, $log) {
                if ($field->has_multiple_files) {
                    $log->{$field->name}->each(
                        fn ($file) => $message->attach($file->getLocalPath())
                    );

                    return true;
                }

                $message->attach($log->{$field->name}->getLocalPath());
            });
    }

    protected function sendAutoresponderMessage()
    {
        $email = $this->data[$this->form->response_email] ?? null;
        $name = $this->data[$this->form->response_name] ?? null;

        if (! $this->form->response_template_code || ! $email) {
            return;
        }

        Mail::send($this->form->response_template_code, $this->data, function (Message $message) use ($email, $name) {
            if ($this->form->is_autoresponder_log_enabled) {
                $this->setHeaders($message);
            }

            $message->to($email, $name);
        });
    }
}
