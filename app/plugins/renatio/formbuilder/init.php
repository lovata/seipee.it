<?php

use Renatio\FormBuilder\Listeners\LogEmailHtml;
use Renatio\FormBuilder\Listeners\SendEmailMessage;

Event::listen('formBuilder.formSubmitted', SendEmailMessage::class);
Event::listen('mailer.send', LogEmailHtml::class);
