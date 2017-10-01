<?php


/**
 * Class SlackInvite
 *
 * @property string $Name
 * @property string $Email
 * @property boolean $Invited
 */
class SlackInvite extends DataObject implements PermissionProvider
{

    private static $db = [
        'Name'    => 'Varchar(255)',
        'Email'   => 'Varchar(255)',
        'Invited' => 'Boolean(false)'
    ];

    private static $summary_fields = [
        'Created',
        'Name',
        'Email',
        'Invited.Nice'
    ];

    private static $field_labels = [
        'Created'      => 'Invite requested on',
        'Name'         => 'Name',
        'Email'        => 'Email address',
        'Invited.Nice' => 'Invite successful'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        if (!$this->Invited) {
            $fields->addFieldToTab(
                'Root.Main',
                LiteralField::create(
                    'resend',
                    _t('SlackInvite.Resend', '<p>To resend an invite, click the resend button in the overview</p>')
                )
            );
        }

        return $fields;
    }

    /**
     * Re-send this invite
     * @throws \ValidationException
     */
    public function reSend()
    {
        /** @var SlackSignupForm $form */
        $form = Injector::inst()->get('SlackSignupForm');
        $form->inviteUser($this);
    }

    /**
     * Permissions
     *
     * @return array
     */
    public function providePermissions()
    {
        return [
            'EDIT_SLACKINVITE'   => [
                'name'     => _t('SlackInvite.PERMISSION_EDIT_DESCRIPTION', 'Edit Slack invites'),
                'category' => _t('Permissions.SLACK_SLACKINVITE', 'Slack permissions'),
                'help'     => _t('SlackInvite.PERMISSION_EDIT_HELP', 'Permission required to edit existing Slack invites.')
            ],
            'DELETE_SLACKINVITE' => [
                'name'     => _t('SlackInvite.PERMISSION_DELETE_DESCRIPTION', 'Delete Slack invites'),
                'category' => _t('Permissions.SLACK_SLACKINVITE', 'Slack permissions'),
                'help'     => _t('SlackInvite.PERMISSION_DELETE_HELP', 'Permission required to delete existing Slack invites.'
                )
            ],
            'VIEW_SLACKINVITE'   => [
                'name'     => _t('SlackInvite.PERMISSION_VIEW_DESCRIPTION', 'View Slack invites'),
                'category' => _t('Permissions.SLACK_SLACKINVITE', 'Slack permissions'),
                'help'     => _t('SlackInvite.PERMISSION_VIEW_HELP', 'Permission required to view existing Slack invites.')
            ],
        ];
    }


    /**
     * Don't create them from the CMS
     * {@inheritdoc}
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * Edit is useful for if someone mis-typed it's email address
     * {@inheritdoc}
     */
    public function canEdit($member = null)
    {
        return Permission::checkMember($member, array('EDIT_SLACKINVITE', 'CMS_ACCESS_SlackInviteAdmin'));
    }

    /**
     * {@inheritdoc}
     */
    public function canDelete($member = null)
    {
        return Permission::checkMember($member, array('DELETE_SLACKINVITE', 'CMS_ACCESS_SlackInviteAdmin'));
    }

    /**
     * {@inheritdoc}
     */
    public function canView($member = null)
    {
        return Permission::checkMember($member, array('VIEW_SLACKINVITE', 'CMS_ACCESS_SlackInviteAdmin'));
    }
}