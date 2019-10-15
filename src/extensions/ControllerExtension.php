<?php

namespace Firesphere\StripeSlack\Extension;

use Firesphere\StripeSlack\Form\SlackSignupForm;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\Extension;

/**
 * Class SlackControllerExtension
 *
 * @property ContentController $owner
 */
class ControllerExtension extends Extension
{
    private static $allowed_actions = [
        'SlackForm'
    ];

    public function SlackForm()
    {
        return SlackSignupForm::create($this->owner, __FUNCTION__);
    }
}
