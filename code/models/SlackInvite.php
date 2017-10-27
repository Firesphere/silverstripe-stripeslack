<?php


/**
 * Class SlackInvite
 *
 * @property string $Name
 * @property string $Email
 * @property boolean $Invited
 * @property string $Message
 */
class SlackInvite extends DataObject implements PermissionProvider
{
    private static $db = [
        'Name'    => 'Varchar(255)',
        'Email'   => 'Varchar(255)',
        'Invited' => 'Boolean(false)',
        'Message' => 'Varchar(255)',
    ];

    private static $summary_fields = [
        'Created',
        'Name',
        'Email',
        'Invited.Nice',
        'Message'
    ];

    private static $field_labels = [
        'Created'      => 'Invite requested on',
        'Name'         => 'Name',
        'Email'        => 'Email address',
        'Invited.Nice' => 'Invite successful'
    ];

    private static $messages = [
        'not_authed'        => 'No valid Slack Token provided, please check your settings',
        'already_invited'   => 'User has already received an email invitation',
        'already_in_team'   => 'User is already part of the team',
        'channel_not_found' => 'Provided channel ID does not match an existing channel in your workspace',
        'sent_recently'     => 'When using resend=true, the email has been sent recently already',
        'user_disabled'     => 'User account has been deactivated',
        'missing_scope'     => 'Using an access_token not authorized for "client" scope',
        'invalid_email'     => 'Invalid email address (e.g. "qwe"). Note that Slack does not recognize some email addresses even though they are technically valid. This is a known issue.',
        'not_allowed'       => 'When SSO is enabeld this method can not be used to invite new users except guests. The SCIM API needs to be used instead to invite new users. '
    ];

    private static $better_buttons_actions = [
        'resendInvite'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Invited']);
        if (!$this->Invited) {
            $fields->addFieldToTab(
                'Root.Main',
                LiteralField::create(
                    'resend',
                    _t('SlackInvite.Resend', '<p>To resend an invite, click the resend button in the overview</p>')
                )
            );
        }
        $fields->addFieldToTab('Root.Main', ReadonlyField::create('Message', 'API Message'));
        $fields->addFieldToTab(
            'Root.Main',
            ReadonlyField::create('InvitedStatus', 'Invite successful', $this->dbObject('Invited')->Nice())
        );

        return $fields;
    }

    /**
     * If BetterButtons is installed, add a button to resend or retry
     * @return mixed
     */
    public function getBetterButtonsActions()
    {
        $fields = parent::getBetterButtonsActions();
        if ($this->Invited) {
            $fields->push(
                BetterButtonCustomAction::create('resendInvite', 'Resend')
                ->setRedirectType(BetterButtonCustomAction::REFRESH)
            );
        } else {
            $fields->push(
                BetterButtonCustomAction::create('resendInvite', 'Retry')
                ->setRedirectType(BetterButtonCustomAction::REFRESH)
            );
        }

        return $fields;
    }

    /**
     * If the user isn't invited yet, send out the invite
     * @throws ValidationException
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // Only attempt to send when there is no ID
        // This prevents retrying from the CMS from ending up in an endless loop
        if (!$this->ID) {
            $this->inviteUser();
        }
    }


    /**
     * This method is public, so it can be addressed from the CMS.
     *
     * @param bool $resend
     * @throws ValidationException
     */
    public function inviteUser($resend = false)
    {
        /** @var SiteConfig $config */
        $config = SiteConfig::current_site_config();
        // Break if there is a configuration error
        if (!$config->SlackURL || !$config->SlackToken || !$config->SlackChannel) {
            $this->Invited = false;
        } else {
            /** @var RestfulService $service with an _uncached_ response */
            $params = [
                'token'      => $config->SlackToken,
                'type'       => 'post',
                'email'      => $this->Email,
                'set_active' => true,
                'channel'    => $config->SlackChannel,
                'scope'      => 'identify,read,post,client',
            ];

            if ($resend) {
                $params['resend'] = true;
            }
            if ($this->Name) {
                $params['first_name'] = $this->Name;
            }

            $this->doRequestEmail($config, $params);
        }
    }


    /**
     * @param SiteConfig $config
     * @param array $params
     * @return bool|$this
     * @throws \ValidationException
     */
    private function doRequestEmail($config, $params)
    {
        $now = time();
        $service = RestfulService::create($config->SlackURL, 0);

        $response = $service->request('/api/users.admin.invite?t=' . $now, 'POST', $params);
        $result = Convert::json2array($response->getBody());

        if (isset($result['error'])) {
            /** @noinspection PhpParamsInspection */
            SS_Log::log($result['error'], SS_Log::ERR);
            $this->Message = static::$messages[$result['error']];

            if ($result['error'] === 'already_invited' || $result['error'] === 'already_in_team') {
                $this->Message .= '; Invite successful';
                $this->Invited = true;
            }
        } else {
            $this->Message = 'Invite successful';
            $this->Invited = (bool)$result['ok'];
        }

        if ($this->Invited) {
            $this->deleteDuplicates();
        }

        $isModelAdmin = Controller::curr() instanceof ModelAdmin;
        /*
         * Only write here if we're in the CMS, don't write if the invite failed
         * As that will cause a possible infinite loop
         */
        if ((bool)$this->Invited === true && $isModelAdmin) {
            $this->write();

            return $this->Message;
        } elseif ($isModelAdmin) {
            return $this->Message;
        }

        return $this;
    }

    /**
     * Remove duplicates after a successful invite
     */
    public function deleteDuplicates()
    {
        /** @var DataList|SlackInvite[] $thisDuplicates */
        $thisDuplicates = static::get()
            ->filter(['Email' => $this->Email, 'Invited' => false])
            ->exclude(['ID' => $this->ID]);

        if ($this->Invited === true && $thisDuplicates->count() > 0) {
            // This user tried multiple times, now that Invited is true, let's delete the others
            $thisDuplicates->removeAll();
        }
    }

    /**
     * Re-send this invite
     * @throws ValidationException
     */
    public function resendInvite()
    {
        // Resend the invite. If the user has been invited before it should re-send
        // The instance is needed so we know if we should write inside the `inviteUser` method
        $this->inviteUser((bool)$this->Invited);
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
                'help'     => _t(
                    'SlackInvite.PERMISSION_EDIT_HELP',
                    'Permission required to edit existing Slack invites.'
                )
            ],
            'DELETE_SLACKINVITE' => [
                'name'     => _t('SlackInvite.PERMISSION_DELETE_DESCRIPTION', 'Delete Slack invites'),
                'category' => _t('Permissions.SLACK_SLACKINVITE', 'Slack permissions'),
                'help'     => _t(
                    'SlackInvite.PERMISSION_DELETE_HELP',
                    'Permission required to delete existing Slack invites.'
                )
            ],
            'VIEW_SLACKINVITE'   => [
                'name'     => _t('SlackInvite.PERMISSION_VIEW_DESCRIPTION', 'View Slack invites'),
                'category' => _t('Permissions.SLACK_SLACKINVITE', 'Slack permissions'),
                'help'     => _t(
                    'SlackInvite.PERMISSION_VIEW_HELP',
                    'Permission required to view existing Slack invites.'
                )
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
