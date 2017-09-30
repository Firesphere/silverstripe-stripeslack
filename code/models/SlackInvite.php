<?php


/**
 * Class SlackInvite
 *
 * @property string $Name
 * @property string $Email
 * @property boolean $Invited
 */
class SlackInvite extends DataObject
{

    private static $db = [
        'Name'    => 'Varchar(255)',
        'Email'   => 'Varchar(255)',
        'Invited' => 'Boolean(false)'
    ];

    private static $summary_fields = [
        'Name',
        'Email',
        'Invited.Nice'
    ];

    private static $field_labels = [
        'Name' => 'Name',
        'Email' => 'Email address',
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
                    '<p>To resend an invite, please for now, manually do it from Slack</p>'
                )
            );
        }
    }
}