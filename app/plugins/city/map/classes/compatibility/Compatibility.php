<?php namespace City\Map\Classes\Compatibility;

class Compatibility
{
    /**
     * Apply for the CMS version less than 2.0
     */
    public function apply(): bool
    {
        if (class_exists('\System\Facades\System')
            && version_compare(\System\Facades\System::VERSION, '2.0', '>=')
        ) {
            return false;
        }

        return true;
    }
}
