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
class Magpleasure_Adminlogger_Model_Observer_Cmsstaticblocks extends Magpleasure_Adminlogger_Model_Observer
{
    public function CmsBlocksLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('cmsstaticblocks')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cmsstaticblocks::ACTION_BLOCK_LOAD,
            Mage::app()->getRequest()->getParam('block_id')
        );
    }

    public function CmsBlocksSave($event)
    {
        $blocks = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('cmsstaticblocks')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cmsstaticblocks::ACTION_BLOCK_SAVE,
            $blocks->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($blocks->getData(), $blocks->getOrigData())
            );
        }
    }

    public function CmsBlocksDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('cmsstaticblocks')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cmsstaticblocks::ACTION_BLOCK_DELETE
        );
    }

}