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
class Magpleasure_Adminlogger_Model_Observer_Indexmanagement extends Magpleasure_Adminlogger_Model_Observer
{

    /**
     * Prepares Names of Indexes
     *
     * @param array $ids
     * @return array
     */
    protected function _getIndexNames(array $ids)
    {
        $names = array();
        foreach ($ids as $processId){
            /* @var $process Mage_Index_Model_Process */
            $process = Mage::getModel('index/process')->load($processId);
            $names[] = $process->getData('indexer_code');
        }
        return $names;
    }

    public function IndexManagementMassReindex($event)
    {
        $massReindex = Mage::app()->getRequest()->getPost('process');
        $log = $this->createLogRecord(
            $this->getActionGroup('indexmanagement')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Indexmanagement::ACTION_INDEX_MANAGEMENT_MASS_REINDEX
        );

        $massReindex = $this->_getIndexNames($massReindex);

        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($massReindex));
        }
    }


    public function IndexManagementMassChangeMode($event)
    {
        $massReindex = Mage::app()->getRequest()->getPost('process');
        $log = $this->createLogRecord(
            $this->getActionGroup('indexmanagement')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Indexmanagement::ACTION_INDEX_MANAGEMENT_MASS_CHANGE_MODE
        );

        $massReindex = $this->_getIndexNames($massReindex);

        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($massReindex));
        }
    }

    public function IndexManagementReindexProcess($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('indexmanagement')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Indexmanagement::ACTION_INDEX_MANAGEMENT_REINDEX,
            Mage::app()->getRequest()->getParam('process')
        );
    }

    public function IndexManagementSave($event)
    {
        $index = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('indexmanagement')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Indexmanagement::ACTION_INDEX_MANAGEMENT_SAVE,
            $index->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($index->getData(), $index->getOrigData())
            );
        }
    }
}