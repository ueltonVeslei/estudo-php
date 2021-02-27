<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
class Magpleasure_Adminlogger_Model_Observer_Systembackups extends Magpleasure_Adminlogger_Model_Observer
{


    public function SystemBackupsCreate($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('systembackups')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Systembackups::ACTION_SYSTEM_BACKUPS_CREATE
        );
    }

    public function SystemBackupsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('systembackups')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Systembackups::ACTION_SYSTEM_BACKUPS_DELETE
        );
    }
}
