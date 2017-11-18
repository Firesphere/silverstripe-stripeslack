<?php

namespace Firesphere\StripeSlack\Actions;

use Firesphere\StripeSlack\Model\SlackInvite;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * class GridfieldInviteResendAction adds the resend button to the CMS for easy re-inviting
 */
class GridfieldInviteResendAction implements GridField_ColumnProvider, GridField_ActionProvider
{

    /**
     * @param GridField $gridField
     * @param array $columns
     */
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns, true)) {
            $columns[] = 'Actions';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'col-buttons'];
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName === 'Actions') {
            return ['title' => ''];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    /**
     * @param GridField $gridField
     * @param SlackInvite $record
     * @param string $columnName
     * @return DBHTMLText
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        $config = SiteConfig::current_site_config();
        // No point in showing the re-send button, if there's no token
        if ($config->SlackToken) {
            $field = $this->getGridField($gridField, $record);

            if (!$record->Invited) {
                $field
                    ->setAttribute('title', 'Retry invite')
                    ->setAttribute('data-icon', 'arrow-circle-135-left')
                    ->setDescription(_t('GridfieldInviteResendAction.Resend', 'Retry failed invitation'));
            } else {
                $field
                    ->setAttribute('title', 'Resend invite')
                    ->setAttribute('data-icon', 'arrow-circle-double')
                    ->setDescription('Resend invite');
            }

            return $field->Field();
        }
    }

    /**
     * @param $gridField
     * @param $record
     * @return GridField_FormAction
     */
    private function getGridField($gridField, $record)
    {
        $field = GridField_FormAction::create(
            $gridField,
            'Resend' . $record->ID,
            false,
            'resend',
            ['RecordID' => $record->ID]
        )->addExtraClass('gridfield-button-resend');

        return $field;
    }

    /**
     * {@inheritDoc}
     */
    public function getActions($gridField)
    {
        return ['resend'];
    }

    /**
     * @param GridField $gridField
     * @param $actionName
     * @param $arguments
     * @param $data
     * @throws ValidationException
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName === 'resend') {
            /** @var SlackInvite $item */
            $item = SlackInvite::get()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }

            $result = $item->resendInvite();
            if ($result) {
                Controller::curr()->getResponse()->setStatusCode(
                    200,
                    'User successfully invited.'
                );
            } else {
                Controller::curr()->getResponse()->setStatusCode(
                    200,
                    $result
                );
            }
        }
    }
}
