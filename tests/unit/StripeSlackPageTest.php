<?php

namespace Firesphere\StripeSlack\Test;

use Firesphere\StripeSlack\Controller\StripeSlackPageController;
use Firesphere\StripeSlack\Form\SlackSignupForm;
use Firesphere\StripeSlack\Page\StripeSlackPage;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class StripeSlackPageTest extends SapphireTest
{
    public function testCMSFields()
    {
        $class = StripeSlackPage::create();
        $fields = $class->getCMSFields();

        $this->assertInstanceOf(FieldList::class, $fields);
    }

    public function testSlackSignupForm()
    {
        $this->assertInstanceOf(SlackSignupForm::class, StripeSlackPageController::create()->SlackSignupForm());
    }

    public function testSlackSignupFormSuccess()
    {
        $this->assertInstanceOf(StripeSlackPageController::class, StripeSlackPageController::create()->success());
    }

    public function testSlackSignupFormError()
    {
        $this->assertInstanceOf(StripeSlackPageController::class, StripeSlackPageController::create()->oops());
    }
}
