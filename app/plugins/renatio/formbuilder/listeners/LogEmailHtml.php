<?php

namespace Renatio\FormBuilder\Listeners;

use Renatio\FormBuilder\Models\FormLog;

class LogEmailHtml
{
    public function handle($mailer, $view, $message)
    {
        $header = $message->getHeaders()->get('X-RENATIO-LOG');

        $logId = $header ? (int) $header->getValue() : null;

        if (! $logId) {
            return;
        }

        FormLog::query()
            ->where('id', $logId)
            ->update([
                'content_html' => $message->getHtmlBody(),
                'subject' => $message->getSubject(),
                'to' => $this->formatAddresses($message->getTo()),
                'cc' => $this->formatAddresses($message->getCc()),
                'bcc' => $this->formatAddresses($message->getBcc()),
                'from' => $this->formatAddresses($message->getFrom()),
                'ip_address' => request()->ip(),
            ]);
    }

    public function formatAddresses($addresses)
    {
        return collect($addresses)
            ->map(fn($address) => $address->toString())
            ->implode(', ');
    }
}
