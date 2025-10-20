<?php

namespace Renatio\FormBuilder\Traits;

use RainLab\Location\Models\State;

trait SupportLocationFields
{
    public function onChangeCountry()
    {
        return [
            '.state-select' => $this->renderPartial(
                '@state_select_options', ['options' => $this->stateOptions()]
            ),
        ];
    }

    protected function stateOptions()
    {
        return ['' => request('placeholder')] + State::getNameList(request(request('name')));
    }
}
