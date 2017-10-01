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
        $form = parent::getEditForm($id, $fields); // TODO: Change the autogenerated stub
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