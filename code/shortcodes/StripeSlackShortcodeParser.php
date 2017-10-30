<?php


class StripeSlackShortcodeParser
{
    public function stripeslackHandler()
    {
        return SlackSignupForm::create(Controller::curr(), 'SlackForm')->forTemplate();
    }
}
