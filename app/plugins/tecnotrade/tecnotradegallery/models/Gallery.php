<?php namespace Tecnotrade\Tecnotradegallery\Models;

use Model;

/**
 * Model
 */
class Gallery extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'tecnotrade_tecnotradegallery_galleries';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    public function getImagesAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function setImagesAttribute($value)
    {
        $this->attributes['images'] = json_encode($value);
    }

}
