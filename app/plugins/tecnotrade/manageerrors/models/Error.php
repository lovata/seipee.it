<?php namespace Tecnotrade\Manageerrors\Models;

use Model;

/**
 * Model
 */
class Error extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var bool timestamps are disabled.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'tecnotrade_manageerrors_error';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    protected $fillable = ['page_url', 'error_code', 'error_line', 'error_file' , 'error_message', 'error_date', 'import_status'];

}
