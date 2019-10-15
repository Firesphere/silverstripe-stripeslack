<?php

namespace Firesphere\StripeSlack\Controller;

use Firesphere\StripeSlack\Form\SlackSignupForm;
use Firesphere\StripeSlack\Page\StripeSlackPage;
use PageController;

/**
 * Class StripeSlackPage_Controller
 *
 * @property StripeSlackPage dataRecord
 * @method StripeSlackPage data()
 * @mixin StripeSlackPage dataRecord
 */
class StripeSlackPageController extends PageController
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
