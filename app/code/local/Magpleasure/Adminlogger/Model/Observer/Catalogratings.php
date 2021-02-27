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
class Magpleasure_Adminlogger_Model_Observer_Catalogratings extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogRatingsLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogratings')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogratings::ACTION_RATINGS_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CatalogRatingsSave($event)
    {
        $rating = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogratings')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogratings::ACTION_RATINGS_SAVE,
            $rating->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($rating->getData(), $rating->getOrigData())
            );
        }
    }

    public function CatalogRatingsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogratings')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogratings::ACTION_RATINGS_DELETE
        );
    }
}