<?php


class SlackSignupForm extends Form
{

    private $siteConfig;

    /**
     * SlackSignupForm constructor.
     * @param Controller $controller
     * @param string $name
     * @param FieldList $fields
     * @param FieldList $actions
     * @param null $validator
     */
    public function __construct(
        Controller $controller = null,
        $name,
        FieldList $fields = null,
        FieldList $actions = null,
        $validator = null
    ) {
        $this->siteConfig = SiteConfig::current_site_config();
        if (!$controller) {
            $controller = Controller::curr();
        }
        if (!$fields) {
            $fields = $this->getFormFields();
        }
        if (!$actions) {
            $actions = $this->getFormActions();
        }

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    /**
     * @return FieldList
     */
    protected function getFormFields()
    {
        if (!$this->siteConfig->SlackToken) {
            return FieldList::create([
                LiteralField::create('Setup',
                    _t('SlackSignupForm.Setup', 'StripeSlack has not yet been configured correctly'))
            ]);
        }
        $fields = FieldList::create(
            [
                LiteralField::create('Intro',
                    _t('SlackSignupForm.Intro', 'Fill out the form below to request access to Slack')),
                TextField::create('Name', _t('SlackSignupForm.Name', 'My name is')),
                EmailField::create('Email', _t('SlackSignupForm.Email', 'My email address is'))
                    ->setAttribute('required', true),
            ]
        );

        $this->extend('updateFormFields', $fields);

        return $fields;
    }

    /**
     * @return FieldList
     */
    protected function getFormActions()
    {
        if (!$this->siteConfig->SlackToken) {
            return FieldList::create();
        }

        return FieldList::create([
            FormAction::create('submitSlackForm', _t('SlackSignupForm.Submit', 'Submit'))
        ]);
    }

    /**
     * @param array $data
     * @param SlackSignupForm $form
     */
    protected function submitSlackForm($data, $form)
    {
        $signup = SlackInvite::create();
        $form->saveInto($signup);
        $userID = $signup->write();
        /** @var SlackInvite $signup We need to re-fetch from the database after writing */
        $signup = SlackInvite::get()->byID($userID);
        $this->redirectSlack($signup->Invited);
    }

    /**
     * This method seems long, but it's primarily switching between CMS and normal user
     * Plus a check if the URL's are set on the config.
     *
     * @param boolean $success
     * @return bool|SS_HTTPResponse
     */
    public function redirectSlack($success)
    {
        $config = SiteConfig::current_site_config();
        if (!$success) {
            if ($config->SlackErrorBackURLID) {
                return $this->controller->redirect($config->SlackErrorBackURL()->Link());
            }
            $this->controller->redirect($this->controller->Link('error'));
        }
        if ($config->SlackBackURLID) {
            return $this->controller->redirect($config->SlackBackURL()->Link());
        }
        return $this->controller->redirect($this->controller->Link('success'));
    }
}
