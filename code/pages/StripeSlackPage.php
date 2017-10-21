<?php

/**
 * Class StripeSlackPage
 * 
 * This page does not have a unit test, as it's such a basic pagetype
 *
 * @property string $Success
 * @property string $Error
 */
class StripeSlackPage extends Page
{

    private static $description = 'Slack signup page';

    private static $db = [
        'Success' => 'HTMLText',
        'Error' => 'HTMLText'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.SlackMessages', [
            HtmlEditorField::create('Success', 'Success message'),
            HtmlEditorField::create('Error', 'Error message')
        ]);

        return $fields;
    }

}

/**
 * Class StripeSlackPage_Controller
 *
 * @property StripeSlackPage dataRecord
 * @method StripeSlackPage data()
 * @mixin StripeSlackPage dataRecord
 */
class StripeSlackPage_Controller extends Page_Controller
{

    private static $allowed_actions = [
        'SlackSignupForm',
        'success',
        'oops'
    ];

    public function SlackSignupForm()
    {
        return SlackSignupForm::create($this, __FUNCTION__);
    }

    public function success()
    {
        return $this;
    }

    public function oops()
    {
        return $this;
    }
}