<?php

class StripeSlackPageTest extends SapphireTest
{
    public function testCMSFields()
    {
        $class = StripeSlackPage::create();
        $fields = $class->getCMSFields();

        $this->assertInstanceOf('FieldList', $fields);
    }

    public function testSlackSignupForm()
    {
        $this->assertInstanceOf('SlackSignupForm', StripeSlackPage_Controller::create()->SlackSignupForm());
    }

    public function testSlackSignupFormSuccess()
    {
        $this->assertInstanceOf('StripeSlackPage_Controller', StripeSlackPage_Controller::create()->success());
    }

    public function testSlackSignupFormError()
    {
        $this->assertInstanceOf('StripeSlackPage_Controller', StripeSlackPage_Controller::create()->oops());
    }

}