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
class Magpleasure_Adminlogger_Model_Observer_Catalogreviews extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogReviewsLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogreviews')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogreviews::ACTION_REVIEWS_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CatalogReviewsSave($event)
    {
        $review = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogreviews')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogreviews::ACTION_REVIEWS_SAVE,
            $review->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($review->getData(), $review->getOrigData())
            );
        }
    }

    public function CatalogReviewsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogreviews')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogreviews::ACTION_REVIEWS_DELETE
        );
    }
}