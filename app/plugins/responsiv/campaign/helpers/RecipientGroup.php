<?php namespace Responsiv\Campaign\Helpers;

use Event;

class RecipientGroup
{
    public static function listRecipientGroups()
    {
        $result = [];

        $apiResult = Event::fire('responsiv.campaign.listRecipientGroups');
        if (is_array($apiResult)) {
            foreach ($apiResult as $groupList) {
                if (!is_array($groupList)) {
                    continue;
                }

                foreach ($groupList as $code => $name) {
                    $result[$code] = $name;
                }
            }
        }

        return $result;
    }

    public static function getRecipientsData($type)
    {
        $result = [];

        $apiResult = Event::fire('responsiv.campaign.getRecipientsData', [$type]);
        if (is_array($apiResult)) {
            foreach ($apiResult as $data) {
                if (!is_array($data)) {
                    continue;
                }

                $result = $result + $data;
            }
        }

        return $result;
    }
}
