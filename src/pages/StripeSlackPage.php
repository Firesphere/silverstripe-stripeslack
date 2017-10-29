<?php

namespace Firesphere\StripeSlack\Page;

use Page;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

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
        'Error'   => 'HTMLText'
    ];

    private static $table_name = 'StripeSlackPage';

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
