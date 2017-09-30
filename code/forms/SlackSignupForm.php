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
                LiteralField::create('Intro', _t('SlackSignupForm.Intro', 'Fill out the form below to request access to Slack')),
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
     * @param SlackInvite $signup
     */
    private function inviteUser($signup)
    {
        $config = SiteConfig::current_site_config();
        if (!$config->SlackURL || !$config->SlackToken) {
            if ($config->SlackErrorBackURLID) {
                $this->controller->redirect($config->SlackErrorBackURL()->Link());
            } else {
                $this->sessionMessage(_t('SlackSignupForm.ConfigError', 'There is an error in the Slack Configuration'), 'warning');
                $this->controller->redirectBack();
            }
        }
        /** @var RestfulService $service */
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
        $signup->Invited = (bool)$result['ok'];
        $signup->write();
        if ($config->SlackBackURLID) {
            $this->controller->redirect($config->SlackBackURL()->Link());
        } else {
            $this->sessionMessage(_t('SlackSignupForm.NoSuccessPage', 'An invite has been sent to your inbox'), 'good');
            $this->controller->redirectBack();
        }
    }
}
