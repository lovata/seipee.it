<?php namespace City\Map\Classes\Compatibility;

class Grid
{
    public static function fixColumns($list)
    {
        $compatibility = new Compatibility;
        if (! $compatibility->apply()) {
            return;
        }

        foreach ($list->getColumns() as $column) {
            if ('selectable' === $column->type) {
                $column->type = 'text';
                $column->displayFrom = 'type_label';
            }
        }
    }
}
