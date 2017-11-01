<?php

namespace Firesphere\StripeSlack\Admin;

use Firesphere\StripeSlack\Actions\GridfieldInviteResendAction;
use Firesphere\StripeSlack\Model\SlackInvite;
use SilverStripe\Admin\ModelAdmin;

/**
 * Class SlackAdmin
 *
 */
class SlackAdmin extends ModelAdmin
{
    private static $managed_models = [
        SlackInvite::class
    ];

    private static $url_segment = 'SlackInvite';

    private static $menu_title = 'Slack Invites';

    private static $menu_icon = 'firesphere/stripeslack: dist/img/slack_logo.png';


    public function getEditForm($id = null, $fields = null)
    {
        /** @var $this |Form $form */
        $form = parent::getEditForm($id, $fields);
        // Slightly pointless because it only manages 1 model, but ¯\_(ツ)_/¯
        if ($this->modelClass === 'SlackInvite') {
            $form->Fields()
                ->fieldByName('SlackInvite')
                ->getConfig()
                ->addComponent(
                    new GridfieldInviteResendAction()
                );
        }

        return $form;
    }
}
