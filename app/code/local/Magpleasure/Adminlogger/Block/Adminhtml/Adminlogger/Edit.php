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
class Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _helper()
    {
        return Mage::helper('adminlogger');
    }

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'log_id';
        $this->_blockGroup = 'adminlogger';
        $this->_controller = 'adminhtml_adminlogger';

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return $this->__("Summary");
    }

}