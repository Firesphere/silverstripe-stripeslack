<?php

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
        return array('class' => 'col-buttons');
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName === 'Actions') {
            return array('title' => '');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnsHandled($gridField)
    {
        return array('Actions');
    }

    /**
     * @param GridField $gridField
     * @param SlackInvite $record
     * @param string $columnName
     * @return HTMLText
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        $config = SiteConfig::current_site_config();
        $field = GridField_FormAction::create($gridField, 'null', false, '', [])
            ->setAttribute('title', 'Already invited')
            ->setAttribute('data-icon', 'cross-circle_disabled')
            ->setDescription('Invite successful')
            ->setDisabled(true);
        // No point in showing the re-send button, if there's no token
        if ($config->SlackToken) {
            if (!$record->Invited) {
                $field = GridField_FormAction::create(
                    $gridField, 'Resend' . $record->ID, false, 'resend', array('RecordID' => $record->ID)
                )
                    ->addExtraClass('gridfield-button-resend')
                    ->setAttribute('title', 'resend')
                    ->setAttribute('data-icon', 'arrow-circle-135-left')
                    ->setDescription(_t('GridfieldInviteResendAction.Resend', 'Resend invitation'));
            }
        }

        return $field->Field();
    }

    /**
     * {@inheritDoc}
     */
    public function getActions($gridField)
    {
        return array('resend');
    }

    /**
     * @param GridField $gridField
     * @param $actionName
     * @param $arguments
     * @param $data
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName === 'resend') {
            /** @var SlackInvite $item */
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }
            return $item->reSend();
        }
    }

}
