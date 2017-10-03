<?php


class StripeSlackShortcodeParser
{

    public function stripeslackHandler()
    {
        return SlackSignupForm::create()->forTemplate();
    }
}