<?php namespace Tecnotrade\Blocks;

use Cms\Classes\ComponentBase;

use Offline\Mall\Models\Product;

class ComponentProducts extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'ComponentBilancioSociale Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {

        // Access the Boxes page where the component is rendered on.
        if ($this->methodExists('getBoxesPage')) {
            $boxesPage = $this->getBoxesPage();
        }

        // Access the Boxes Box where the component is rendered on.
        if ($this->methodExists('getBoxesBox')) {
            $boxesBox = $this->getBoxesBox();

            // Do something with the information, like overriding a property
            // only if the Component is rendered inside a Box.
            $this->setProperty('category_id', $boxesBox->some_selected_category_id);
        }

        $this->products = Product::all();

    }


    
    // PRIVATE

}