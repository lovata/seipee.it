<?php namespace Responsiv\Campaign\Models;

use Backend\Models\ImportModel;
use ApplicationException;
use Exception;

/**
 * SubscriberImport Model
 */
class SubscriberImport extends ImportModel
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_campaign_subscribers';

    /**
     * Validation rules
     */
    public $rules = [
        'email' => 'required|email',
    ];

    protected $listNameCache = [];

    /**
     * getSubscriberListsOptions
     */
    public function getSubscriberListsOptions()
    {
        return SubscriberList::lists('name', 'id');
    }

    /**
     * importData
     */
    public function importData($results, $sessionKey = null)
    {
        $firstRow = reset($results);

        /*
         * Validation
         */
        if ($this->auto_create_lists && !array_key_exists('lists', $firstRow)) {
            throw new ApplicationException('Please specify a match for the Lists column.');
        }

        /*
         * Import
         */
        foreach ($results as $row => $data) {
            try {

                if (!$email = array_get($data, 'email')) {
                    $this->logSkipped($row, 'Missing email address');
                    continue;
                }

                /*
                 * Find or create
                 */
                $subscriber = Subscriber::firstOrNew(['email' => $email]);
                $subscriberExists = $subscriber->exists;

                /*
                 * Set attributes
                 */
                $except = ['lists'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    $subscriber->{$attribute} = $value ?: null;
                }

                $subscriber->forceSave();

                if ($listIds = $this->getListIdsForSubscriber($data)) {
                    $subscriber->subscriber_lists()->sync($listIds, false);
                }

                /*
                 * Log results
                 */
                if ($subscriberExists) {
                    $this->logUpdated();
                }
                else {
                    $this->logCreated();
                }
            }
            catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }

    }

    /**
     * getListIdsForSubscriber
     */
    protected function getListIdsForSubscriber($data)
    {
        $ids = [];

        if ($this->auto_create_lists) {
            $listNames = $this->decodeArrayValue(array_get($data, 'lists'));

            foreach ($listNames as $name) {
                if (!$name = trim($name)) continue;

                if (isset($this->listNameCache[$name])) {
                    $ids[] = $this->listNameCache[$name];
                }
                else {
                    $newList = SubscriberList::firstOrCreate(['name' => $name]);
                    $ids[] = $this->listNameCache[$name] = $newList->id;
                }
            }
        }
        elseif ($this->subscriber_lists) {
            $ids = (array) $this->subscriber_lists;
        }

        return $ids;
    }
}
