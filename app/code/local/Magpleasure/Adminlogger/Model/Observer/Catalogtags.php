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
class Magpleasure_Adminlogger_Model_Observer_Catalogtags extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogTagsLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogtags')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogtags::ACTION_CATALOG_TAGS_LOAD,
            Mage::app()->getRequest()->getParam('tag_id')
        );
    }

    public function CatalogTagsSave($event)
    {
        $tag = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogtags')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogtags::ACTION_CATALOG_TAGS_SAVE,
            $tag->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($tag->getData(), $tag->getOrigData())
            );
        }
    }

    public function CatalogTagsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogtags')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogtags::ACTION_CATALOG_TAGS_DELETE
        );
    }
}