<?php namespace Lovata\ApiSynchronization\Models;

use Model;

/**
 * SyncSettings Model
 *
 * @property int $id
 * @property int $sync_interval_hours
 * @property int $sync_interval_minutes
 * @property bool $is_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SyncSettings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * @var string Settings code
     */
    public $settingsCode = 'lovata_apisync_settings';

    /**
     * @var string Reference to field configuration
     */
    public $settingsFields = 'fields.yaml';

    /**
     * Initialize default values
     */
    public function initSettingsData()
    {
        $this->sync_interval_hours = 4;
        $this->sync_interval_minutes = 0;
        $this->is_enabled = true;
    }

    /**
     * @var array Validation rules
     */
    public $rules = [
        'sync_interval_hours' => 'required|integer|min:0|max:23',
        'sync_interval_minutes' => 'required|integer|min:0|max:59',
    ];

    /**
     * @var array Validation messages
     */
    public $customMessages = [
        'sync_interval_hours.required' => 'Hours field is required',
        'sync_interval_hours.min' => 'Hours must be at least 0',
        'sync_interval_hours.max' => 'Hours must be no more than 23',
        'sync_interval_minutes.required' => 'Minutes field is required',
        'sync_interval_minutes.min' => 'Minutes must be at least 0',
        'sync_interval_minutes.max' => 'Minutes must be no more than 59',
    ];

    /**
     * Get singleton instance (only one settings record)
     */
    public static function instance()
    {
        $settings = static::first();

        if (!$settings) {
            $settings = static::create([
                'sync_interval_hours' => 4,
                'sync_interval_minutes' => 0,
                'is_enabled' => true,
            ]);
        }

        return $settings;
    }

    /**
     * Get total interval in minutes
     */
    public function getTotalMinutesAttribute()
    {
        return ($this->sync_interval_hours * 60) + $this->sync_interval_minutes;
    }

    /**
     * Get cron expression based on interval
     *
     * @return string
     */
    public function getCronExpression()
    {
        $totalMinutes = $this->total_minutes;

        // If interval is less than 60 minutes - run every N minutes
        if ($totalMinutes < 60) {
            return "*/{$totalMinutes} * * * *";
        }

        // If interval is exactly hours (no minutes) - run every N hours
        if ($this->sync_interval_minutes == 0) {
            return "0 */{$this->sync_interval_hours} * * *";
        }

        // Complex interval - convert to hours and minutes
        // This is approximate, runs every total minutes
        return "*/{$totalMinutes} * * * *";
    }

    /**
     * Get human-readable interval description
     */
    public function getIntervalDescriptionAttribute()
    {
        $parts = [];

        if ($this->sync_interval_hours > 0) {
            $parts[] = $this->sync_interval_hours . ' hour' . ($this->sync_interval_hours > 1 ? 's' : '');
        }

        if ($this->sync_interval_minutes > 0) {
            $parts[] = $this->sync_interval_minutes . ' minute' . ($this->sync_interval_minutes > 1 ? 's' : '');
        }

        return implode(' and ', $parts);
    }
}

