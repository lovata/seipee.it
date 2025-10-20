<?php namespace City\Map\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use City\Map\Classes\Compatibility\Form;

class Maps extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'city.map.maps'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('City.Map', 'map', 'maps');
    }

    public function listInjectRowClass($object, $definition = null)
    {
        if (! $object->is_active) {
            return 'disabled';
        }
    }

    public function formExtendFields($form)
    {
        Form::fixFields($form);
    }
}
