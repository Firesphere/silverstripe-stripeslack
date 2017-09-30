<?php


/**
 * Class SlackAdmin
 *
 */
class SlackAdmin extends ModelAdmin
{

    private static $managed_models = [
        'SlackInvite'
    ];

    private static $url_segment = 'SlackInvite';

    private static $menu_title = 'Slack Invites';
}