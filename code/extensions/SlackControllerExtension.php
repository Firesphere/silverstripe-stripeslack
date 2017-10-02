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
        if(SiteConfig::current_site_config()->SlackToken) {
            return SlackSignupForm::create($this->owner, __FUNCTION__);
        }
        return null;
    }
}