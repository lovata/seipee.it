<?php

namespace Renatio\FormBuilder\Models;

use October\Rain\Database\Model;
use October\Rain\Database\Traits\Validation;
use System\Behaviors\SettingsModel;

class Settings extends Model
{
    use Validation;

    public $implement = [SettingsModel::class];

    public $settingsCode = 'renatio_formbuilder_settings';

    public $settingsFields = 'fields.yaml';

    public $rules = [
        'prune_logs_period' => ['integer', 'nullable'],
    ];

    public function initSettingsData()
    {
        $this->site_key = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';

        $this->secret_key = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';

        $this->prune_logs_period = 30;
    }
}
