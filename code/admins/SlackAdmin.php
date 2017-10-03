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

    private static $menu_icon = '/stripeslack/img/slack_logo.png';


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