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
    ];

    private static $has_one = [
        'SlackBackURL'      => SiteTree::class,
        'SlackErrorBackURL' => SiteTree::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['SlackToken']);
        $fields->addFieldsToTab('Root.Slack', [
            $url = TextField::create('SlackURL', 'URL Of the Slack channel'),
            $channel = TextField::create('SlackChannel', 'The ID of your channel'),
            TextField::create('SlackClientID', 'Client ID for your Slack App'),
            PasswordField::create('SlackClientSecret', 'Client Secret for your Slack App'),
            TreeDropdownField::create('SlackBackURLID', 'URL to redirect when request is successful', 'SiteTree'),
            TreeDropdownField::create('SlackErrorBackURLID', 'URL to redirect when request is unsuccessful',
                'SiteTree'),
        ]);
        if (
            $this->owner->SlackURL &&
            $this->owner->SlackClientID &&
            $this->owner->SlackClientSecret &&
            !$this->owner->SlackToken
        ) {
            $domain = Director::absoluteURL('/SlackAuthorization');
            $text = LiteralField::create(
                'link',
                '<p><a href="' . $this->owner->SlackURL . '/oauth/authorize?client_id=' . $this->owner->SlackClientID . '&scope=client&back_url=' . $domain . '">' .
                'To activate your StripeSlack, Please have an admin activate StripeSlack by clicking on this link.' .
                '</a></p>'
            );
            $fields->addFieldToTab('Root.Slack', $text);
        }
        $channel->setDescription('You can get the ID by right clicking on your channel and select "copy link". Open the copied link in a browser and copy the part after "messages/" in to this field');
        $url->setDescription('Include the "https://" part');

        $fields->addFieldToTab('Root.Slack',
            LiteralField::create('instructions', '<p><a href="https://github.com/Firesphere/silverstripe-stripeslack/blob/master/readme.md">Extensive instructions can be found on GitHub</a></p>'));
        $fields->addFieldToTab('Root.Slack',
            $secretField = CheckboxField::create('ClearSecrets', 'Clear Secrets and Tokens'));
        $secretField->setDescription('Checking this checkbox will clear out the Client Secret and (invisible in the CMS) Client Token. In case your admin left the group, or you want a new admin to sent the invite');
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->owner->ClearSecrets) {
            $this->owner->SlackClientSecret = '';
            $this->owner->SlackToken = '';
        }
    }
}