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
class Magpleasure_Adminlogger_Model_Observer_Mpblogtag extends Magpleasure_Adminlogger_Model_Observer
{
    public function MpBlogTagLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpblogtag')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpblogtag::ACTION_TAG_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function MpBlogTagSave($event)
    {
        if (Mage::registry('adminlogger_post_save')){
            return $this;
        }

        $tag = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('mpblogtag')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpblogtag::ACTION_TAG_SAVE,
            $tag->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($tag->getData(), $tag->getOrigData())
            );
        }

    }

    public function MpBlogTagDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpblogtag')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpblogtag::ACTION_TAG_DELETE
        );
    }
}
