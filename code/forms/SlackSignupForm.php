<?php


class SlackSignupForm extends Form
{

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
     *
     * @param boolean $success
     * @return bool|SS_HTTPResponse
     */
    public function redirectSlack($success)
    {
        $config = SiteConfig::current_site_config();
        $controller = Controller::curr();
        if ($controller instanceof ModelAdmin) {
            // In theory, this shows a message in the CMS
            // In practice, it seems to do nothing
            if ($success === true) {
                return $controller->getResponse()->setStatusCode(
                    200,
                    'User successfully invited.'
                );
            } else {
                return $controller->getResponse()->setStatusCode(
                    500,
                    'Something went wrong when inviting the user.'
                );
            }        }
        if (!$success) {
            if ($config->SlackErrorBackURLID) {
                return $this->controller->redirect($config->SlackErrorBackURL()->Link());
            } else {
                $this->sessionMessage(
                    _t('SlackSignupForm.ConfigError', 'There is an error in the Slack Configuration'),
                    'warning'
                );
                return $this->controller->redirectBack();
            }
        }
        if ($config->SlackBackURLID) {
            return $this->controller->redirect($config->SlackBackURL()->Link());
        } else {
            $this->sessionMessage(_t('SlackSignupForm.NoSuccessPage', 'An invite has been sent to your inbox'), 'good');
            return $this->controller->redirectBack();
        }

    }
}
