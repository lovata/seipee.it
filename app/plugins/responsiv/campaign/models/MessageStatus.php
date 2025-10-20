<?php namespace Responsiv\Campaign\Models;

use Model;

/**
 * MessageStatus Model
 */
class MessageStatus extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ACTIVE = 'active';
    const STATUS_SENT = 'sent';
    const STATUS_DELETED = 'cancelled';
    const STATUS_ARCHIVED = 'archived';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_campaign_message_statuses';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var Collection Cache of all records
     */
    public static $recordCache;

    /**
     * getDraftStatus
     */
    public static function getDraftStatus()
    {
        return self::getFromCode(self::STATUS_DRAFT);
    }

    /**
     * getPendingStatus
     */
    public static function getPendingStatus()
    {
        return self::getFromCode(self::STATUS_PENDING);
    }

    /**
     * getProcessingStatus
     */
    public static function getProcessingStatus()
    {
        return self::getFromCode(self::STATUS_PROCESSING);
    }

    /**
     * getActiveStatus
     */
    public static function getActiveStatus()
    {
        return self::getFromCode(self::STATUS_ACTIVE);
    }

    /**
     * getSentStatus
     */
    public static function getSentStatus()
    {
        return self::getFromCode(self::STATUS_SENT);
    }

    /**
     * getCancelledStatus
     */
    public static function getCancelledStatus()
    {
        return self::getFromCode(self::STATUS_DELETED);
    }

    /**
     * getArchivedStatus
     */
    public static function getArchivedStatus()
    {
        return self::getFromCode(self::STATUS_ARCHIVED);
    }

    /**
     * getStatusCodeOptions
     */
    public function getStatusCodeOptions()
    {
        return [
            'draft' => ['Draft', '#98a0a0'],
            'pending' => ['Pending', 'var(--bs-info)'],
            'processing' => ['Pending', 'var(--bs-info)'],
            'active' => ['Active', 'var(--bs-primary)'],
            'sent' => ['Sent', 'var(--bs-success)'],
            'cancelled' => ['Cancelled', 'var(--bs-danger)'],
            'archived' => ['Archived', 'var(--bs-black)'],
        ];
    }

    /**
     * getFromCode
     */
    public static function getFromCode($code)
    {
        return self::listAll()->first(function($status, $key) use ($code) {
            return $status->code == $code;
        });
    }

    /**
     * listAll
     */
    public static function listAll()
    {
        if (self::$recordCache !== null) {
            return self::$recordCache;
        }

        return self::$recordCache = self::all();
    }

    /**
     * __toString the code is more useful than returning JSON.
     */
    public function __toString()
    {
        return $this->code;
    }
}
