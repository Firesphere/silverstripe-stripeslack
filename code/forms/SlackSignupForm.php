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
        return FieldList::create(
            [
                LiteralField::create('Intro',
                    _t('SlackSignupForm.Intro', 'Fill out the form below to request access to Slack')),
                TextField::create('Name', _t('SlackSignupForm.Name', 'My name is')),
                EmailField::create('Email', _t('SlackSignupForm.Email', 'My email address is'))
            ]
        );
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
        $signup->ID = $signup->write();
        $this->inviteUser($signup);
    }

    /**
     * This method is public, so it can be addressed from the CMS.
     *
     *
     * @param SlackInvite $signup
     * @throws ValidationException
     */
    public function inviteUser($signup)
    {
        /** @var SiteConfig $config */
        $config = SiteConfig::current_site_config();
        // Break if there is a configuration error
        if (!$config->SlackURL || !$config->SlackToken || !$config->SlackChannel) {
            $this->redirectSlack(false, $config);
        }
        /** @var RestfulService $service with an _uncached_ response */
        $service = RestfulService::create($config->SlackURL, 0);
        $params = [
            'token'      => $config->SlackToken,
            'type'       => 'post',
            'email'      => $signup->Email,
            'set_active' => true,
            'channel'    => $config->SlackChannel,
            'scope'      => 'identify,read,post,client'
        ];
        $now = time();

        $response = $service->request('/api/users.admin.invite?t=' . $now, 'POST', $params);
        $result = Convert::json2array($response->getBody());

        if (isset($result['error']) && $result['error'] === 'already_invited') {
            $signup->Invited = true;
        } else {
            $signup->Invited = (bool)$result['ok'];
        }

        $signup->ID = $signup->write();

        $this->updateDuplicates($signup);

        $this->redirectSlack(true, $config);
    }

    /**
     *
     * @param boolean $success
     * @param SiteConfig $config
     * @return bool|SS_HTTPResponse
     */
    public function redirectSlack($success, $config)
    {
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

    /**
     * @param SlackInvite $signup
     * @throws \ValidationException
     */
    public function updateDuplicates($signup)
    {
        /** @var DataList|SlackInvite[] $signupDuplicates */
        $signupDuplicates = SlackInvite::get()->filter(['Email' => $signup->Email, 'Invited' => false]);

        if ($signup->Invited === true && $signupDuplicates->count() > 0) {
            // This user tried multiple times, now that Invited is true, let's set them all to true
            foreach ($signupDuplicates as $duplicate) {
                $duplicate->Invited = true;
                $duplicate->write();
            }
        }
    }
}
