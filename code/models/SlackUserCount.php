<?php

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
}
