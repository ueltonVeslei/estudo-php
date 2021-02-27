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
class Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Helper
     *
     * @return Magpleasure_Adminlogger_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('adminlogger');
    }

    public function __construct()
    {
        parent::__construct();
        $this->setId('adminLoggerGrid');
        $this->setDefaultSort('log_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setIdFieldName('log_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('adminlogger/log')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('log_id', array(
            'header' => Mage::helper('adminlogger')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'log_id',
        ));

        $this->addColumn('action_time', array(
            'header' => Mage::helper('adminlogger')->__('Action Time'),
            'index' => 'action_time',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('action_group', array(
            'header' => Mage::helper('adminlogger')->__('Action Group'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'action_group',
            'type' => 'options',
            'options' => $this->_helper()->getActionGroups(),
        ));

        $types = $this->_helper()->getAllActionTypes();
        $group  = $this->_getFilterValue('action_group');
        $options = $group ? @$types[$group] : array();

        $this->addColumn('action_type', array(
            'header' => Mage::helper('adminlogger')->__('Action Type'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'action_type',
            'sortable' => false,
            'type' => 'options',
            'renderer' => 'adminlogger/adminhtml_widget_grid_renderer_actiontype',
            'options' => $options,
        ));

        $this->addColumn('user_id', array(
            'header' => Mage::helper('adminlogger')->__('User'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'user_id',
            'type' => 'options',
            'options' => $this->_helper()->getUsers(),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('adminlogger')->__('Store View Filter'),
                'index' => 'store_id',
                'align' => 'right',
                'width' => '50px',
                'type' => 'options',
                'options' => $this->_helper()->getStores(),
            ));
        }

        $this->addColumn('ip_address', array(
            'header'    => $this->_helper()->__('IP Address'),
            'default'   => $this->_helper()->__('n/a'),
            'index'     => 'remote_addr',
            'renderer'  => 'adminhtml/customer_online_grid_renderer_ip',
            'filter'    => false,
            'sort'      => false,
            'width' => '50px',
        ));

        $this->addColumn('action',
            array(
                'header' => Mage::helper('adminlogger')->__('Action'),
                'width' => '100',
                'getter' => 'getId',
                'type' => 'action',
                'align' => 'right',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('adminlogger')->__('Details'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id',
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'is_system' => true,
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('adminlogger')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminlogger')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _getFilterValue($key)
    {
        $filter = $this->getRequest()->getParam($this->getVarNameFilter()) ?
                  $this->getRequest()->getParam($this->getVarNameFilter()) :
                  $this->getParam($this->getVarNameFilter());
        $filter = base64_decode($filter);
        parse_str(urldecode($filter), $data);
        return isset($data[$key]) ?  $data[$key] : NULL;
    }

    public function getAdditionalJavaScript()
    {
        $data = Zend_Json::encode($this->_helper()->getAllActionTypes());
        $filter = $this->_getFilterValue('action_type');
        $start = $this->_getFilterValue('action_group') ? 'true' : 'false';
        return "
        var initActionTypeFilter = function(start){
            new MpAdminloggerActionTypeFilter({
                group_select: 'adminLoggerGrid_filter_action_group',
                type_select: 'adminLoggerGrid_filter_action_type',
                data: {$data},
                selected_type: '{$filter}',
            });
        };

        initActionTypeFilter({$start});

        adminLoggerGridJsObject.initCallback = function(grid){
            initActionTypeFilter(false);
        };

        ";
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}