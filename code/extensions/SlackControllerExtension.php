<?php


/**
 * Class SlackControllerExtension
 *
 * @property ContentController $owner
 */
class SlackControllerExtension extends Extension
{
    private static $allowed_actions = [
        'SlackForm'
    ];

    public function SlackForm()
    {
        return SlackSignupForm::create($this->owner, __FUNCTION__);
    }
}