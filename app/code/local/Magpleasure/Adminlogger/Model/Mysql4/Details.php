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
class Magpleasure_Adminlogger_Model_Mysql4_Details extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('adminlogger/details', 'detail_id');
    }

    protected function _getType($value)
    {
        if (is_object($value)){
            if ($value->__toString()){
                return 'text';
            } else {
                return false;
            }
        }

        if (is_array($value)){
            try {
                $value = serialize($value);
            } catch (Exception $e){
                return false;
            }
        }

        if (!is_null($value)){
            if (is_integer($value)){
                return 'int';
            }  elseif (is_float($value)) {
                return 'decimal';
            } elseif (is_string($value) && strlen($value) < 254) {
                return 'varchar';
            } elseif (is_string($value)){
                return 'text';
            }
        }
        return false;
    }

    protected function _saveData($detailId, $direction, $type, $value)
    {
        try {
            $table = $this->getMainTable()."_{$type}";
            $writeAdapret = $this->_getWriteAdapter()->beginTransaction();
            $writeAdapret->insert($table, array(
                'detail_id' => $detailId,
                'direction' => $direction,
                'value' => $value,
            ));
            $writeAdapret->commit();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        $from = $object->getData('from');
        if (!is_null($from) && ($type = $this->_getType($from))){
            $this->_saveData($object->getId(), Magpleasure_Adminlogger_Model_Details::DIRECTION_FROM, $type, $from);
        }

        $to = $object->getData('to');
        if (!is_null($to) && ($type = $this->_getType($to))){
            $this->_saveData($object->getId(), Magpleasure_Adminlogger_Model_Details::DIRECTION_TO, $type, $to);
        }
    }

    protected function _loadType(Mage_Core_Model_Abstract$object, $type)
    {
        try {
            $table = $this->getMainTable()."_{$type}";
            $readAdapret = $this->_getReadAdapter();

            $select = $readAdapret->select();
            $select
                ->from($table, '*')
                ->where("detail_id = ?", $object->getId())
                ;

            foreach ($readAdapret->fetchAll($select) as $data){
                $object->setData( ($data['direction'] == Magpleasure_Adminlogger_Model_Details::DIRECTION_FROM ? 'from' : 'to'), $data['value'] );
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);
        foreach (array('int', 'decimal', 'varchar', 'text') as $type){
            $this->_loadType($object, $type);
        }
    }
}