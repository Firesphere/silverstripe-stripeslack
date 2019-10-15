<?php

namespace Firesphere\StripeSlack\Model;

use SilverStripe\ORM\DataObject;

/**
 * Class SlackUserCount
 *
 * @property int $UserCount
 */
class SlackUserCount extends DataObject
{
    private static $db = [
        'UserCount' => 'Int',
    ];

    private static $table_name = 'SlackUserCount';
}
