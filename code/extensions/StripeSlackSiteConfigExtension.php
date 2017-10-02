<?php


/**
 * Class StripeSlackSiteConfigExtension
 *
 * @property SiteConfig|StripeSlackSiteConfigExtension $owner
 * @property string $SlackURL
 * @property string $SlackClientID
 * @property string $SlackClientSecret
 * @property string $SlackChannel
 * @property string $SlackToken
 * @property boolean $ClearSecrets
 * @property int $SlackBackURLID
 * @property int $SlackErrorBackURLID
 * @method SiteTree SlackBackURL()
 * @method SiteTree SlackErrorBackURL()
 */
class StripeSlackSiteConfigExtension extends DataExtension
{

    private static $db = [
        'SlackURL'          => 'Varchar(255)',
        'SlackClientID'     => 'Varchar(255)',
        'SlackClientSecret' => 'Varchar(255)',
        'SlackChannel'      => 'Varchar(255)',
        'SlackToken'        => 'Varchar(255)',
        // This is here to support the IDE's
        'ClearSecrets'      => 'Boolean(false)'
    ];

    private static $has_one = [
        'SlackBackURL'      => SiteTree::class,
        'SlackErrorBackURL' => SiteTree::class
    ];

    private static $helptexts = [
        'Link'         => 'To activate your StripeSlack, Please have an admin activate StripeSlack by clicking on this link.',
        'ClearSecrets' => 'Checking this checkbox will clear out the Client Secret and (invisible in the CMS) Client Token. In case your admin left the group, or you want a new admin to sent the invite',
        'Channel'      => 'You can get the ID by right clicking on your channel and select "copy link". Open the copied link in a browser and copy the part after "messages/" in to this field',
        'URLHelp'      => 'Include the "https://" part',
        'Extended'     => '<p><a href="https://github.com/Firesphere/silverstripe-stripeslack/blob/master/readme.md">Extensive instructions can be found on GitHub</a></p>'
    ];

    public function updateFieldLabels(&$labels)
    {
        $labels['SlackURL'] = _t('StripeSlackSiteConfigExtension.SlackURL', 'URL Of the Slack channel');
        $labels['SlackClientID'] = _t('StripeSlackSiteConfigExtension.SlackClientID', 'Client ID for your Slack App');
        $labels['SlackClientSecret'] = _t('StripeSlackSiteConfigExtension.SlackClientSecret',
            'Client Secret for your Slack App');
        $labels['SlackChannel'] = _t('StripeSlackSiteConfigExtension.SlackChannel', 'The ID of your channel');
        $labels['ClearSecrets'] = _t('StripeSlackSiteConfigExtension.ClearSecrets', 'Clear Secrets and Tokens');
        $labels['SlackBackURL'] = _t('StripeSlackSiteConfigExtension.SlackBackURL',
            'URL to redirect when request is successful');
        $labels['SlackErrorBackURL'] = _t('StripeSlackSiteConfigExtension.SlackErrorBackURL',
            'URL to redirect when request is unsuccessful');
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['SlackToken']);
        $fields->addFieldsToTab('Root.Slack', [
            $url = TextField::create('SlackURL', $this->owner->fieldLabel('SlackURL')),
            $channel = TextField::create('SlackChannel', $this->owner->fieldLabel('SlackChannel')),
            TextField::create('SlackClientID', $this->owner->fieldLabel('SlackClientID')),
            PasswordField::create('SlackClientSecret', $this->owner->fieldLabel('SlackClientSecret')),
            TreeDropdownField::create('SlackBackURLID', $this->owner->fieldLabel('SlackBackURL'), 'SiteTree'),
            TreeDropdownField::create('SlackErrorBackURLID', $this->owner->fieldLabel('SlackErrorBackURL'), 'SiteTree'),
        ]);
        if (
            $this->owner->SlackURL &&
            $this->owner->SlackClientID &&
            $this->owner->SlackClientSecret &&
            !$this->owner->SlackToken
        ) {
            $domain = Director::absoluteURL('/SlackAuthorization/');
            $text = LiteralField::create(
                'link',
                '<p><a href="' . $this->owner->SlackURL . '/oauth/authorize?client_id=' . $this->owner->SlackClientID . '&scope=client&redirect_uri=' . $domain . '">' .
                static::$helptexts['Link'] .
                '</a></p>'
            );
            $fields->addFieldToTab('Root.Slack', $text);
        } else {
            $fields->addFieldToTab('Root.Slack',
                $secretField = CheckboxField::create('ClearSecrets', $this->owner->fieldLabel('ClearSecrets')));
            $secretField->setDescription(static::$helptexts['ClearSecrets']);
        }
        $channel->setDescription(static::$helptexts['Channel']);
        $url->setDescription(static::$helptexts['URLHelp']);

        $fields->addFieldToTab('Root.Slack', LiteralField::create('instructions', static::$helptexts['Extended']));
    }

    /**
     * Clear out the secrets if the checkbox is checked
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->owner->ClearSecrets) {
            $this->owner->SlackClientSecret = '';
            $this->owner->SlackToken = '';
        }
        // Always set back to false so the checkbox won't stay ticked
        $this->owner->ClearSecrets = false;
    }
}
